@php
    // Get organization settings from database
    $companyName = $organizationSettings['company_name']->value ?? 'Not set';
    $tradingName = $organizationSettings['trading_name']->value ?? 'Not set';
    $address = $organizationSettings['address']->value ?? 'Not set';
    $phone = $organizationSettings['phone']->value ?? 'Not set';
    $email = $organizationSettings['email']->value ?? 'Not set';
    $website = $organizationSettings['website']->value ?? 'Not set';
    $taxId = $organizationSettings['tax_id']->value ?? 'Not set';
    
    // Get organization settings from OrganizationSetting table
    $orgSettings = $orgSettings ?? $settings ?? null;
@endphp

<div class="row">
    <!-- Financial Year Settings Card -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="bx bx-calendar"></i> Financial Year Configuration</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light" onclick="toggleFinancialYearLock()">
                        <i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }}"></i> 
                        {{ $orgSettings->financial_year_locked ? 'Unlock' : 'Lock' }} Financial Year
                    </button>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#financialYearModal">
                        <i class="bx bx-cog"></i> Configure
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center p-4 border rounded bg-gradient bg-primary text-white">
                            <i class="bx bx-calendar-check bx-lg mb-2"></i>
                            <h6 class="mb-1">Current Financial Year</h6>
                            <h3 class="mb-0 fw-bold">{{ $orgSettings->current_financial_year }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-4 border rounded bg-light">
                            <i class="bx bx-calendar bx-lg text-info mb-2"></i>
                            <h6 class="text-info mb-1">Start Date</h6>
                            <h5 class="mb-0 fw-bold">{{ $orgSettings->financial_year_start_date ? $orgSettings->formatDate($orgSettings->financial_year_start_date) : 'Not Set' }}</h5>
                            <small class="text-muted">{{ $orgSettings->financial_year_start_month }}/{{ $orgSettings->financial_year_start_day }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-4 border rounded bg-light">
                            <i class="bx bx-calendar-event bx-lg text-success mb-2"></i>
                            <h6 class="text-success mb-1">End Date</h6>
                            <h5 class="mb-0 fw-bold">{{ $orgSettings->financial_year_end_date ? $orgSettings->formatDate($orgSettings->financial_year_end_date) : 'Not Set' }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-4 border rounded bg-light">
                            <i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }} bx-lg text-{{ $orgSettings->financial_year_locked ? 'danger' : 'success' }} mb-2"></i>
                            <h6 class="text-{{ $orgSettings->financial_year_locked ? 'danger' : 'success' }} mb-1">Status</h6>
                            <span class="badge bg-{{ $orgSettings->financial_year_locked ? 'danger' : 'success' }} badge-lg">
                                {{ $orgSettings->financial_year_locked ? 'Locked' : 'Active' }}
                            </span>
                            @php
                                $daysRemaining = $orgSettings->financial_year_end_date 
                                    ? \Carbon\Carbon::parse($orgSettings->financial_year_end_date)->diffInDays(now(), false) 
                                    : null;
                            @endphp
                            @if($daysRemaining !== null)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ $daysRemaining > 0 ? $daysRemaining . ' days remaining' : abs($daysRemaining) . ' days overdue' }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if(isset($financialYearHistory) && count($financialYearHistory) > 0)
                <div class="mt-4">
                    <h6 class="mb-3"><i class="bx bx-history"></i> Financial Year History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
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
                @endif
            </div>
        </div>
    </div>

    <!-- Organization Information - Advanced Structure -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="bx bx-building"></i> Organization Information</h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#organizationInfoModal">
                    <i class="bx bx-edit"></i> Edit Information
                </button>
            </div>
            <div class="card-body">
                <!-- Basic Information Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="bx bx-info-circle"></i> Basic Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Company Legal Name</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-building text-primary me-2"></i>
                                <strong>{{ $companyName }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Trading Name</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-store text-info me-2"></i>
                                <strong>{{ $tradingName }}</strong>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted small">Company Address</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-map text-success me-2"></i>
                                {{ $address }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="bx bx-phone"></i> Contact Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Phone Number</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-phone text-primary me-2"></i>
                                <a href="tel:{{ $phone }}" class="text-decoration-none">{{ $phone }}</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Email Address</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-envelope text-danger me-2"></i>
                                <a href="mailto:{{ $email }}" class="text-decoration-none">{{ $email }}</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Website</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-globe text-info me-2"></i>
                                <a href="{{ Str::startsWith($website, 'http') ? $website : 'https://' . $website }}" target="_blank" class="text-decoration-none">
                                    {{ $website }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tax Identification Number</label>
                            <div class="p-2 bg-light rounded border">
                                <i class="bx bx-receipt text-warning me-2"></i>
                                {{ $taxId }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency & Regional Settings -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-gradient bg-success text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-dollar"></i> Currency & Regional Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Currency</label>
                        <div class="p-2 bg-light rounded border">
                            <strong>{{ $orgSettings->currency }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Symbol</label>
                        <div class="p-2 bg-light rounded border">
                            <strong>{{ $orgSettings->currency_symbol }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Position</label>
                        <div class="p-2 bg-light rounded border">
                            {{ ucfirst($orgSettings->currency_position) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Decimal Places</label>
                        <div class="p-2 bg-light rounded border">
                            {{ $orgSettings->decimal_places }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Timezone</label>
                        <div class="p-2 bg-light rounded border">
                            <i class="bx bx-time-five me-2"></i>
                            {{ $orgSettings->timezone }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Locale</label>
                        <div class="p-2 bg-light rounded border">
                            {{ $orgSettings->locale }} ({{ $orgSettings->country_code }})
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logo & Branding -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient bg-warning text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="bx bx-image"></i> Company Logo</h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#logoModal">
                    <i class="bx bx-upload"></i> Upload
                </button>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <img src="{{ $orgSettings->company_logo ? asset('storage/' . $orgSettings->company_logo) : asset('assets/img/logo.png') }}" 
                         alt="Company Logo" class="img-fluid rounded shadow" style="max-height: 200px; max-width: 100%; border: 2px solid #dee2e6;">
                </div>
                <small class="text-muted">Recommended: 300x300px, PNG/JPG</small>
            </div>
        </div>

        <!-- System Status -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-gradient bg-dark text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-info-circle"></i> System Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span><i class="bx bx-server text-primary"></i> Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span><i class="bx bx-hdd text-info"></i> Storage</span>
                    <span class="badge bg-success">Available</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span><i class="bx bx-envelope text-danger"></i> Email Service</span>
                    <span class="badge bg-{{ $orgSettings->email_notifications_enabled ? 'success' : 'warning' }}">
                        {{ $orgSettings->email_notifications_enabled ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span><i class="bx bx-message text-success"></i> SMS Service</span>
                    <span class="badge bg-{{ $orgSettings->sms_notifications_enabled ? 'success' : 'warning' }}">
                        {{ $orgSettings->sms_notifications_enabled ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bx bx-code-alt text-secondary"></i> Version</span>
                    <span class="badge bg-info">{{ app()->version() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Organization Information Edit Modal -->
<div class="modal fade" id="organizationInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="organizationInfoForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-edit"></i> Edit Organization Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> All information is stored in the database and can be updated at any time.
                    </div>
                    
                    <h6 class="text-primary mb-3 border-bottom pb-2">Basic Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="organization_settings[company_name]" class="form-control" value="{{ $companyName }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trading Name</label>
                            <input type="text" name="organization_settings[trading_name]" class="form-control" value="{{ $tradingName }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="organization_settings[address]" class="form-control" rows="2">{{ $address }}</textarea>
                    </div>
                    
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Contact Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="organization_settings[phone]" class="form-control" value="{{ $phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="organization_settings[email]" class="form-control" value="{{ $email }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" name="organization_settings[website]" class="form-control" value="{{ $website }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax ID</label>
                            <input type="text" name="organization_settings[tax_id]" class="form-control" value="{{ $taxId }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery
    if (typeof jQuery === 'undefined') {
        setTimeout(arguments.callee, 100);
        return;
    }
    
    const $ = jQuery;
    
    $('#organizationInfoForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
        url: '{{ route("settings.update") }}',
        method: 'PUT',
        data: formData,
        success: function(response) {
            if(response.success) {
                $('#organizationInfoModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Organization information updated successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            let errorMsg = xhr.responseJSON?.message || 'Error updating organization information';
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
</script>
