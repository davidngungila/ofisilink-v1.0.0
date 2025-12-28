@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Organization Settings - OfisiLink')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Organization Settings Management</h4>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1"><i class="bx bx-building text-primary"></i> Organization Settings</h3>
                            <p class="text-muted mb-0">Manage all organization-wide settings that affect the entire system</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                                <i class="bx bx-save"></i> Save All Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <form id="organizationSettingsForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column - Main Settings -->
            <div class="col-lg-8">
                <!-- Company Information Section -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-primary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-building"></i> Company Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Legal Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control" 
                                       value="{{ $orgSettings->company_name ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="company_registration_number" class="form-control" 
                                       value="{{ $orgSettings->company_registration_number ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax ID / VAT Number</label>
                                <input type="text" name="company_tax_id" class="form-control" 
                                       value="{{ $orgSettings->company_tax_id ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="company_email" class="form-control" 
                                       value="{{ $orgSettings->company_email ?? '' }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Company Address</label>
                                <textarea name="company_address" class="form-control" rows="2">{{ $orgSettings->company_address ?? '' }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="company_city" class="form-control" 
                                       value="{{ $orgSettings->company_city ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State / Region</label>
                                <input type="text" name="company_state" class="form-control" 
                                       value="{{ $orgSettings->company_state ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="company_postal_code" class="form-control" 
                                       value="{{ $orgSettings->company_postal_code ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="company_country" class="form-control" 
                                       value="{{ $orgSettings->company_country ?? 'Tanzania' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="company_phone" class="form-control" 
                                       value="{{ $orgSettings->company_phone ?? '' }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Website</label>
                                <input type="url" name="company_website" class="form-control" 
                                       value="{{ $orgSettings->company_website ?? '' }}" 
                                       placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Year Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-danger text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-calendar"></i> Financial Year Configuration
                        </h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleFinancialYearLock()">
                                <i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }}"></i> 
                                {{ $orgSettings->financial_year_locked ? 'Unlock' : 'Lock' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle"></i> <strong>Critical Settings:</strong> Financial year changes affect all accounting, reporting, and compliance features.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Financial Year Start Month <span class="text-danger">*</span></label>
                                <select name="financial_year_start_month" class="form-control" required>
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                            {{ ($orgSettings->financial_year_start_month ?? '07') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                                    </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Financial Year Start Day <span class="text-danger">*</span></label>
                                <select name="financial_year_start_day" class="form-control" required>
                                    @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                            {{ ($orgSettings->financial_year_start_day ?? '01') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Current Financial Year <span class="text-danger">*</span></label>
                                <input type="number" name="current_financial_year" class="form-control" 
                                       value="{{ $orgSettings->current_financial_year ?? date('Y') }}" 
                                       min="2000" max="2100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Financial Year Start Date</label>
                                <input type="date" class="form-control" 
                                       value="{{ $orgSettings->financial_year_start_date ? $orgSettings->financial_year_start_date->format('Y-m-d') : '' }}" 
                                       readonly>
                                <small class="text-muted">Auto-calculated based on settings above</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Financial Year End Date</label>
                                <input type="date" class="form-control" 
                                       value="{{ $orgSettings->financial_year_end_date ? $orgSettings->financial_year_end_date->format('Y-m-d') : '' }}" 
                                       readonly>
                                <small class="text-muted">Auto-calculated based on settings above</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="initialize_dates" id="initialize_dates" checked>
                                    <label class="form-check-label" for="initialize_dates">
                                        Automatically calculate start and end dates when saving
                                    </label>
                                </div>
                            </div>
                        </div>
                        @if(isset($financialYearHistory) && count($financialYearHistory) > 0)
                        <div class="mt-4">
                            <h6 class="mb-3"><i class="bx bx-history"></i> Financial Year History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
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

                <!-- Currency & Regional Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-success text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-dollar"></i> Currency & Regional Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Currency Code <span class="text-danger">*</span></label>
                                <input type="text" name="currency" class="form-control" 
                                       value="{{ $orgSettings->currency ?? 'TZS' }}" maxlength="3" required>
                                <small class="text-muted">ISO 4217 code (e.g., TZS, USD, EUR)</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Currency Symbol <span class="text-danger">*</span></label>
                                <input type="text" name="currency_symbol" class="form-control" 
                                       value="{{ $orgSettings->currency_symbol ?? 'TSh' }}" maxlength="10" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Symbol Position <span class="text-danger">*</span></label>
                                <select name="currency_position" class="form-control" required>
                                    <option value="prefix" {{ ($orgSettings->currency_position ?? 'prefix') == 'prefix' ? 'selected' : '' }}>Before (Prefix)</option>
                                    <option value="suffix" {{ ($orgSettings->currency_position ?? 'prefix') == 'suffix' ? 'selected' : '' }}>After (Suffix)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Decimal Places <span class="text-danger">*</span></label>
                                <input type="number" name="decimal_places" class="form-control" 
                                       value="{{ $orgSettings->decimal_places ?? 2 }}" min="0" max="4" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number Format</label>
                                <select name="number_format" class="form-control">
                                    <option value="1,234.56" {{ ($orgSettings->number_format ?? '1,234.56') == '1,234.56' ? 'selected' : '' }}>1,234.56 (US/UK Format)</option>
                                    <option value="1.234,56" {{ ($orgSettings->number_format ?? '1,234.56') == '1.234,56' ? 'selected' : '' }}>1.234,56 (European Format)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Timezone <span class="text-danger">*</span></label>
                                <select name="timezone" class="form-control" required>
                                    @php
                                        $timezones = [
                                            'Africa/Dar_es_Salaam' => 'Dar es Salaam (GMT+3)',
                                            'Africa/Nairobi' => 'Nairobi (GMT+3)',
                                            'UTC' => 'UTC (GMT+0)',
                                            'America/New_York' => 'New York (GMT-5)',
                                            'Europe/London' => 'London (GMT+0)',
                                        ];
                                        $currentTz = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
                                    @endphp
                                    @foreach($timezones as $tz => $label)
                                    <option value="{{ $tz }}" {{ $currentTz == $tz ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Locale</label>
                                <input type="text" name="locale" class="form-control" 
                                       value="{{ $orgSettings->locale ?? 'en' }}" placeholder="en">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country Code</label>
                                <input type="text" name="country_code" class="form-control" 
                                       value="{{ $orgSettings->country_code ?? 'TZ' }}" maxlength="2" placeholder="TZ">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date & Time Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-info text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-time"></i> Date & Time Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date Format <span class="text-danger">*</span></label>
                                <select name="date_format" class="form-control" required>
                                    <option value="Y-m-d" {{ ($orgSettings->date_format ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2024-12-31)</option>
                                    <option value="d/m/Y" {{ ($orgSettings->date_format ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (31/12/2024)</option>
                                    <option value="m/d/Y" {{ ($orgSettings->date_format ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (12/31/2024)</option>
                                    <option value="d-m-Y" {{ ($orgSettings->date_format ?? 'Y-m-d') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY (31-12-2024)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time Format <span class="text-danger">*</span></label>
                                <select name="time_format" class="form-control" required>
                                    <option value="H:i:s" {{ ($orgSettings->time_format ?? 'H:i:s') == 'H:i:s' ? 'selected' : '' }}>24 Hour (14:30:00)</option>
                                    <option value="h:i A" {{ ($orgSettings->time_format ?? 'H:i:s') == 'h:i A' ? 'selected' : '' }}>12 Hour (02:30 PM)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Week Start Day <span class="text-danger">*</span></label>
                                <select name="week_start_day" class="form-control" required>
                                    <option value="monday" {{ ($orgSettings->week_start_day ?? 'monday') == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="sunday" {{ ($orgSettings->week_start_day ?? 'monday') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">First Day of Month</label>
                                <select name="first_day_of_month" class="form-control">
                                    <option value="1" {{ ($orgSettings->first_day_of_month ?? 1) == 1 ? 'selected' : '' }}>1st Day</option>
                                    <option value="0" {{ ($orgSettings->first_day_of_month ?? 1) == 0 ? 'selected' : '' }}>Business Day</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Hours -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-warning text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-time-five"></i> Business Hours
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business Hours Start <span class="text-danger">*</span></label>
                                <input type="time" name="business_hours_start" class="form-control" 
                                       value="{{ $orgSettings->business_hours_start ? \Carbon\Carbon::parse($orgSettings->business_hours_start)->format('H:i') : '09:00' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Hours End <span class="text-danger">*</span></label>
                                <input type="time" name="business_hours_end" class="form-control" 
                                       value="{{ $orgSettings->business_hours_end ? \Carbon\Carbon::parse($orgSettings->business_hours_end)->format('H:i') : '17:00' }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Business Days</label>
                                <div class="row">
                                    @php
                                        $businessDays = $orgSettings->business_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                        $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
                                                'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                                    @endphp
                                    @foreach($days as $key => $label)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="business_days[]" 
                                                   value="{{ $key }}" id="day_{{ $key }}"
                                                   {{ in_array($key, $businessDays) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="day_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-secondary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-money"></i> Payroll Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payroll Period (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="payroll_period_days" class="form-control" 
                                       value="{{ $orgSettings->payroll_period_days ?? 30 }}" min="1" max="365" required>
                                <small class="text-muted">Number of days in a payroll period</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payroll Processing Day <span class="text-danger">*</span></label>
                                <input type="number" name="payroll_processing_day" class="form-control" 
                                       value="{{ $orgSettings->payroll_processing_day ?? 25 }}" min="1" max="31" required>
                                <small class="text-muted">Day of month when payroll is processed</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payroll Currency</label>
                                <input type="text" name="payroll_currency" class="form-control" 
                                       value="{{ $orgSettings->payroll_currency ?? ($orgSettings->currency ?? 'TZS') }}" maxlength="3">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-calendar-check"></i> Leave Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Default Annual Leave Days <span class="text-danger">*</span></label>
                                <input type="number" name="default_annual_leave_days" class="form-control" 
                                       value="{{ $orgSettings->default_annual_leave_days ?? 21 }}" min="0" max="365" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Sick Leave Days <span class="text-danger">*</span></label>
                                <input type="number" name="default_sick_leave_days" class="form-control" 
                                       value="{{ $orgSettings->default_sick_leave_days ?? 30 }}" min="0" max="365" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Consecutive Leave Days</label>
                                <input type="number" name="max_consecutive_leave_days" class="form-control" 
                                       value="{{ $orgSettings->max_consecutive_leave_days ?? 14 }}" min="1" max="365">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-danger text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-receipt"></i> Tax Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">VAT Rate (%)</label>
                                <input type="number" name="vat_rate" class="form-control" 
                                       value="{{ $orgSettings->vat_rate ?? '' }}" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Income Tax Rate (%)</label>
                                <input type="number" name="income_tax_rate" class="form-control" 
                                       value="{{ $orgSettings->income_tax_rate ?? '' }}" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tax Inclusive Pricing</label>
                                <input type="hidden" name="tax_inclusive_pricing" value="0">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="tax_inclusive_pricing" 
                                           id="tax_inclusive_pricing" value="1"
                                           {{ ($orgSettings->tax_inclusive_pricing ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax_inclusive_pricing">
                                        Prices include tax
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-dark text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-file"></i> File Upload Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Max File Size (MB) <span class="text-danger">*</span></label>
                                <input type="number" name="max_file_size" class="form-control" 
                                       value="{{ $orgSettings->max_file_size ?? 10 }}" min="1" max="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Allowed File Types</label>
                                <input type="text" name="allowed_file_types" class="form-control" 
                                       value="{{ $orgSettings->allowed_file_types ?? 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx' }}" 
                                       placeholder="jpg,jpeg,png,pdf">
                                <small class="text-muted">Comma-separated list of allowed file extensions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-info text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-bell"></i> Notification Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="hidden" name="email_notifications_enabled" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notifications_enabled" 
                                           id="email_notifications_enabled" value="1"
                                           {{ (\App\Models\SystemSetting::getValue('email_notifications_enabled', '1') == '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications_enabled">
                                        <i class="bx bx-envelope"></i> Email Notifications
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" name="sms_notifications_enabled" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sms_notifications_enabled" 
                                           id="sms_notifications_enabled" value="1"
                                           {{ (\App\Models\SystemSetting::getValue('sms_notifications_enabled', '0') == '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notifications_enabled">
                                        <i class="bx bx-message"></i> SMS Notifications
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" name="push_notifications_enabled" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="push_notifications_enabled" 
                                           id="push_notifications_enabled" value="1"
                                           {{ (\App\Models\SystemSetting::getValue('push_notifications_enabled', '0') == '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="push_notifications_enabled">
                                        <i class="bx bx-bell"></i> Push Notifications
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Providers Management -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-white">
                                <i class="bx bx-cog"></i> Notification Providers Management
                            </h5>
                            <small class="text-white-50">Configure multiple Email and SMS providers. Set one as primary for system use.</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-light me-2" onclick="loadNotificationProviders()">
                                <i class="bx bx-refresh"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="openProviderModal('email')">
                                <i class="bx bx-plus"></i> Add Email Provider
                            </button>
                            <button type="button" class="btn btn-sm btn-info" onclick="openProviderModal('sms')">
                                <i class="bx bx-plus"></i> Add SMS Provider
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle"></i> 
                            <strong>How it works:</strong> Configure multiple notification providers (Email/SMS) and set one as primary. 
                            The system will use the primary provider for all notifications. You can test each provider and switch between them as needed.
                        </div>
                        
                        <!-- Providers Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Primary</th>
                                        <th>Priority</th>
                                        <th>Connection</th>
                                        <th>Last Tested</th>
                                        <th class="text-center" width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="providersTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Legacy Configuration (Hidden by default, shown if no providers exist) -->
                <div class="card shadow-sm border-0 mb-4" id="legacyConfigSection" style="display: none;">
                    <div class="card-header bg-gradient bg-warning text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-info-circle"></i> Legacy Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle"></i> 
                            This is a legacy configuration section. It's recommended to use the Notification Providers Management above for better control.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mailer Type <span class="text-danger">*</span></label>
                                <select name="mail_mailer" id="mail_mailer" class="form-control" required>
                                    <option value="smtp" {{ \App\Models\SystemSetting::getValue('mail_mailer', config('mail.default')) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ \App\Models\SystemSetting::getValue('mail_mailer', config('mail.default')) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun" {{ \App\Models\SystemSetting::getValue('mail_mailer', config('mail.default')) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ \App\Models\SystemSetting::getValue('mail_mailer', config('mail.default')) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" name="mail_host" id="mail_host" class="form-control" 
                                       value="{{ \App\Models\SystemSetting::getValue('mail_host', config('mail.mailers.smtp.host', '')) }}" 
                                       placeholder="smtp.gmail.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" name="mail_port" id="mail_port" class="form-control" 
                                       value="{{ \App\Models\SystemSetting::getValue('mail_port', config('mail.mailers.smtp.port', 587)) }}" 
                                       placeholder="587" min="1" max="65535" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Encryption <span class="text-danger">*</span></label>
                                <select name="mail_encryption" id="mail_encryption" class="form-control" required>
                                    <option value="tls" {{ \App\Models\SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')) == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ \App\Models\SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="mail_username" id="mail_username" class="form-control" 
                                       value="{{ \App\Models\SystemSetting::getValue('mail_username', config('mail.mailers.smtp.username', '')) }}" 
                                       placeholder="your-email@gmail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Password</label>
                                <div class="input-group">
                                    <input type="password" name="mail_password" id="mail_password" class="form-control" 
                                           value="{{ \App\Models\SystemSetting::getValue('mail_password', '') }}" 
                                           placeholder="Enter SMTP password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('mail_password')">
                                        <i class="bx bx-show" id="mail_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" 
                                       value="{{ \App\Models\SystemSetting::getValue('mail_from_address', config('mail.from.address', '')) }}" 
                                       placeholder="noreply@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name <span class="text-danger">*</span></label>
                                <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" 
                                       value="{{ \App\Models\SystemSetting::getValue('mail_from_name', config('mail.from.name', 'OfisiLink')) }}" 
                                       placeholder="OfisiLink" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integration Settings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-success text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-plug"></i> Integration Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle"></i> Integration settings are stored as JSON. Use this for API keys and third-party service configurations.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Integration Settings (JSON)</label>
                            <textarea name="integration_settings" class="form-control" rows="6" 
                                      placeholder='{"api_key": "your-key", "webhook_url": "https://..."}'>{{ $orgSettings->integration_settings ? json_encode($orgSettings->integration_settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                            <small class="text-muted">Enter valid JSON format</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column - Branding & Status -->
            <div class="col-lg-4">
                <!-- Company Logo -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-warning text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-image"></i> Company Logo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img id="logoPreview" 
                                 src="{{ $orgSettings->company_logo && Storage::disk('public')->exists($orgSettings->company_logo) ? asset('storage/' . $orgSettings->company_logo) : asset('assets/img/office_link_logo.png') }}" 
                                 alt="Company Logo" class="img-fluid rounded shadow" 
                                 style="max-height: 200px; max-width: 100%; border: 2px solid #dee2e6;"
                                 onerror="this.src='{{ asset('assets/img/office_link_logo.png') }}'">
                        </div>
                        <input type="file" name="company_logo" id="company_logo" class="form-control" 
                               accept="image/*" onchange="previewLogo(this)">
                        <small class="text-muted d-block mt-2">Recommended: 300x300px, PNG/JPG</small>
                    </div>
                </div>

                <!-- Company Favicon -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-secondary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-star"></i> Company Favicon
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img id="faviconPreview" 
                                 src="{{ $orgSettings->company_favicon && Storage::disk('public')->exists($orgSettings->company_favicon) ? asset('storage/' . $orgSettings->company_favicon) : asset('assets/img/favicon/favicon.ico') }}" 
                                 alt="Company Favicon" class="img-fluid rounded shadow" 
                                 style="max-height: 64px; max-width: 64px; border: 2px solid #dee2e6;"
                                 onerror="this.src='{{ asset('assets/img/favicon/favicon.ico') }}'">
                        </div>
                        <input type="file" name="company_favicon" id="company_favicon" class="form-control" 
                               accept="image/*" onchange="previewFavicon(this)">
                        <small class="text-muted d-block mt-2">Recommended: 32x32px or 64x64px, ICO/PNG</small>
                    </div>
                </div>

                <!-- System Status & Live Testing -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-dark text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-info-circle"></i> System Status & Live Testing
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- System Status -->
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="bx bx-server"></i> System Status</h6>
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
                                <span id="system_email_status" class="badge bg-secondary">
                                    <i class="bx bx-loader-alt bx-spin"></i> Checking...
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span><i class="bx bx-message text-success"></i> SMS Service</span>
                                <span id="system_sms_status" class="badge bg-secondary">
                                    <i class="bx bx-loader-alt bx-spin"></i> Checking...
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bx bx-code-alt text-secondary"></i> Version</span>
                                <span class="badge bg-info">{{ app()->version() }}</span>
                            </div>
                        </div>
                        
                        <!-- Live Testing Section -->
                        <div class="border-top pt-4">
                            <h6 class="mb-3"><i class="bx bx-test-tube"></i> Live Testing</h6>
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle"></i> <strong>Test your configuration:</strong> Send live test messages to verify your email and SMS settings are working correctly.
                            </div>
                            
                            <!-- Email Testing -->
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1"><i class="bx bx-envelope text-danger"></i> Email Testing</h6>
                                            <small class="text-muted">Test email configuration by sending a test email</small>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="testEmail()">
                                            <i class="bx bx-send"></i> Test Email
                                        </button>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="email" id="quick_test_email" class="form-control" placeholder="Enter email address (e.g., test@example.com)" value="" onkeypress="if(event.key === 'Enter') quickTestEmail();">
                                        <button class="btn btn-outline-danger" type="button" onclick="quickTestEmail()">
                                            <i class="bx bx-send"></i> Send
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SMS Testing -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1"><i class="bx bx-message text-success"></i> SMS Testing</h6>
                                            <small class="text-muted">Test SMS configuration by sending a test SMS</small>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm" onclick="testSMS()">
                                            <i class="bx bx-send"></i> Test SMS
                                        </button>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="text" id="quick_test_phone" class="form-control" placeholder="Enter phone number (e.g., 255712345678)" value="" onkeypress="if(event.key === 'Enter') quickTestSMS();">
                                        <button class="btn btn-outline-success" type="button" onclick="quickTestSMS()">
                                            <i class="bx bx-send"></i> Send
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internal Notes -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient bg-secondary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-note"></i> Internal Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="internal_notes" class="form-control" rows="6" 
                                  placeholder="Add any internal notes or comments about these settings...">{{ $orgSettings->internal_notes ?? '' }}</textarea>
                        <small class="text-muted">These notes are only visible to administrators</small>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient bg-primary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-cog"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="saveAllSettings()">
                                <i class="bx bx-save"></i> Save All Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bx bx-refresh"></i> Reset Form
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="exportSettings()">
                                <i class="bx bx-download"></i> Export Settings
                            </button>
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
// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Initialize form validation
    $('#organizationSettingsForm').on('submit', function(e) {
        e.preventDefault();
        saveAllSettings();
    });
    
    // Check connection status on page load
    checkEmailStatus();
    checkSMSStatus();
});

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logoPreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#faviconPreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleFinancialYearLock() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to {{ $orgSettings->financial_year_locked ? "unlock" : "lock" }} the financial year?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, {{ $orgSettings->financial_year_locked ? "Unlock" : "Lock" }} it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.settings.fy.toggle-lock") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { 
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error updating financial year lock status', 'error');
                }
            });
        }
    });
}

function saveAllSettings() {
    // First save communication settings
    const smsData = {
        sms_username: $('#sms_username').val(),
        sms_password: $('#sms_password').val(),
        sms_from: $('#sms_from').val(),
        sms_url: $('#sms_url').val(),
    };
    
    const emailData = {
        mail_mailer: $('#mail_mailer').val(),
        mail_host: $('#mail_host').val(),
        mail_port: $('#mail_port').val(),
        mail_username: $('#mail_username').val(),
        mail_password: $('#mail_password').val(),
        mail_encryption: $('#mail_encryption').val(),
        mail_from_address: $('#mail_from_address').val(),
        mail_from_name: $('#mail_from_name').val(),
    };
    
    const allCommData = { 
        ...smsData, 
        ...emailData,
        email_notifications_enabled: $('#email_notifications_enabled').is(':checked') ? '1' : '0',
        sms_notifications_enabled: $('#sms_notifications_enabled').is(':checked') ? '1' : '0',
        push_notifications_enabled: $('#push_notifications_enabled').is(':checked') ? '1' : '0'
    };
    
    // Show loading
    Swal.fire({
        title: 'Saving Settings',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Save communication settings first
    $.ajax({
        url: '{{ route('admin.settings.communication.update') }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: allCommData,
        success: function(commResponse) {
            // Then save organization settings
            const form = $('#organizationSettingsForm')[0];
            const formData = new FormData(form);
            
            // Ensure CSRF token is included
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            // Add _method for Laravel PUT request
            formData.append('_method', 'PUT');
            
            // Validate JSON if integration_settings is provided
            const integrationSettings = formData.get('integration_settings');
            if (integrationSettings && integrationSettings.trim()) {
                try {
                    JSON.parse(integrationSettings);
                } catch (e) {
                    Swal.fire('Error', 'Invalid JSON format in Integration Settings', 'error');
                    return;
                }
            }
            
            $.ajax({
                url: '{{ route("settings.update") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if(response.success) {
                        // Update logo preview if logo was uploaded
                        if(response.settings && response.settings.company_logo) {
                            const logoUrl = '{{ asset("storage/") }}/' + response.settings.company_logo;
                            $('#logoPreview').attr('src', logoUrl);
                        }
                        if(response.settings && response.settings.company_favicon) {
                            const faviconUrl = '{{ asset("storage/") }}/' + response.settings.company_favicon;
                            $('#faviconPreview').attr('src', faviconUrl);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'All settings updated successfully',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Recheck connection status after saving
                            checkEmailStatus();
                            checkSMSStatus();
                            // Reload after a short delay to show updated status
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMsg = xhr.responseJSON?.message || 'Error updating organization settings';
                    
                    // If there are validation errors, show them
                    if(Object.keys(errors).length > 0) {
                        errorMsg = '<ul class="text-start">';
                        Object.entries(errors).forEach(([key, messages]) => {
                            messages.forEach(msg => {
                                errorMsg += `<li><strong>${key}:</strong> ${msg}</li>`;
                            });
                        });
                        errorMsg += '</ul>';
                    } else if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMsg,
                        confirmButtonText: 'OK'
                    });
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Error updating communication settings',
                confirmButtonText: 'OK'
            });
        }
    });
}

function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Are you sure you want to reset all changes? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            location.reload();
        }
    });
}

function exportSettings() {
    Swal.fire({
        title: 'Exporting Settings...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch current settings
    fetch('{{ route("settings.data") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.settings) {
            // Create JSON file
            const jsonData = JSON.stringify(data.settings, null, 2);
            const blob = new Blob([jsonData], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'organization-settings-' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'Settings exported successfully',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to export settings',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        console.error('Export error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to export settings: ' + err.message,
            confirmButtonText: 'OK'
        });
    });
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        field.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
}

// Quick test functions for inline testing
function quickTestEmail() {
    const email = document.getElementById('quick_test_email').value.trim();
    if (!email) {
        Swal.fire({
            icon: 'warning',
            title: 'Email Required',
            text: 'Please enter an email address to test',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    sendTestEmail(email);
}

function quickTestSMS() {
    const phone = document.getElementById('quick_test_phone').value.trim();
    if (!phone) {
        Swal.fire({
            icon: 'warning',
            title: 'Phone Number Required',
            text: 'Please enter a phone number to test',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length < 9) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Phone Number',
            text: 'Please enter a valid phone number (at least 9 digits)',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    sendTestSMS(cleanPhone);
}

// Send test email function
function sendTestEmail(email) {
    Swal.fire({
        title: 'Sending Test Email...',
        text: 'Please wait while we send the test email',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route('admin.settings.communication.test-email') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => {
        return res.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
            
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Failed to send test email');
            }
            
            return data;
        });
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                html: `<strong>Test email sent successfully!</strong><br><br>Please check <strong>${email}</strong> inbox (and spam folder) for the test message.`,
                confirmButtonText: 'OK'
            });
            // Clear input
            document.getElementById('quick_test_email').value = '';
        } else {
            throw new Error(data.message || data.error || 'Failed to send test email');
        }
    })
    .catch(error => {
        console.error('Test email error:', error);
        let errorMessage = error.message || 'Unknown error occurred';
        let suggestion = '';
        
        // Get suggestion from error object if available
        if (error.suggestion) {
            suggestion = `<br><br><strong>Troubleshooting Suggestions:</strong><br><pre style="text-align: left; white-space: pre-wrap; font-size: 12px;">${error.suggestion}</pre>`;
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Error Sending Email',
            html: `<strong>Failed to send test email:</strong><br>${errorMessage}${suggestion}`,
            confirmButtonText: 'OK',
            width: '700px'
        });
    });
}

// Send test SMS function
function sendTestSMS(phone) {
    Swal.fire({
        title: 'Sending Test SMS...',
        text: 'Please wait while we send the test SMS',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route('admin.settings.communication.test-sms') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => {
        return res.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
            
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Failed to send test SMS');
            }
            
            return data;
        });
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'SMS Sent!',
                html: `<strong>Test SMS sent successfully!</strong><br><br>Please check phone number <strong>${phone}</strong> for the test message.`,
                confirmButtonText: 'OK'
            });
            // Clear input
            document.getElementById('quick_test_phone').value = '';
        } else {
            throw new Error(data.message || data.error || 'Failed to send test SMS');
        }
    })
    .catch(error => {
        console.error('Test SMS error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error Sending SMS',
            html: `<strong>Failed to send test SMS:</strong><br>${error.message || 'Unknown error occurred'}`,
            confirmButtonText: 'OK'
        });
    });
}

function testSMS() {
    Swal.fire({
        title: 'Test SMS Configuration',
        html: `
            <div class="mb-3">
                <label class="form-label"><strong>Phone Number</strong></label>
                <input type="text" id="test_sms_phone" class="form-control" placeholder="255712345678" required>
                <small class="text-muted">Enter phone number with country code (e.g., 255712345678)</small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-send"></i> Send Test SMS',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        didOpen: () => {
            document.getElementById('test_sms_phone').focus();
        },
        preConfirm: () => {
            const phone = document.getElementById('test_sms_phone').value.trim();
            if (!phone) {
                Swal.showValidationMessage('Please enter a phone number');
                return false;
            }
            
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            if (cleanPhone.length < 9) {
                Swal.showValidationMessage('Please enter a valid phone number (at least 9 digits)');
                return false;
            }
            
            return fetch('{{ route('admin.settings.communication.test-sms') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone: cleanPhone })
            })
            .then(res => {
                return res.text().then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                    
                    if (!res.ok) {
                        throw new Error(data.message || data.error || 'Failed to send test SMS');
                    }
                    
                    return data;
                });
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || data.error || 'Failed to send test SMS');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message || 'Error sending test SMS');
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'SMS Sent Successfully!',
                html: `<strong>Test SMS sent successfully!</strong><br><br>Please check the phone for the message.`,
                confirmButtonText: 'OK'
            });
        }
    });
}

function testEmail() {
    Swal.fire({
        title: 'Test Email Configuration',
        html: `
            <div class="mb-3">
                <label class="form-label"><strong>Email Address</strong></label>
                <input type="email" id="test_email_address" class="form-control" placeholder="test@example.com" required>
                <small class="text-muted">Enter email address to receive test email</small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-send"></i> Send Test Email',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        didOpen: () => {
            document.getElementById('test_email_address').focus();
        },
        preConfirm: () => {
            const email = document.getElementById('test_email_address').value.trim();
            if (!email) {
                Swal.showValidationMessage('Please enter an email address');
                return false;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            return fetch('{{ route('admin.settings.communication.test-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => {
                return res.text().then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                    
                    if (!res.ok) {
                        throw new Error(data.message || data.error || 'Failed to send test email');
                    }
                    
                    return data;
                });
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || data.error || 'Failed to send test email');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message || 'Error sending test email');
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent Successfully!',
                html: `<strong>Test email sent successfully!</strong><br><br>Please check your inbox (and spam folder) for the test message.`,
                confirmButtonText: 'OK'
            });
        }
    });
}

// Check Email connection status
function checkEmailStatus() {
    $.ajax({
        url: '{{ route('admin.settings.communication.check-email') }}',
        method: 'GET',
        timeout: 10000,
        success: function(response) {
            const status = response.status || 'error';
            const message = response.message || 'Unknown status';
            let badgeClass = 'bg-secondary';
            let icon = 'bx-x-circle';
            let text = 'Unknown';
            
            if (status === 'connected') {
                badgeClass = 'bg-success';
                icon = 'bx-check-circle';
                text = 'Connected';
            } else if (status === 'disconnected') {
                badgeClass = 'bg-danger';
                icon = 'bx-x-circle';
                text = 'Disconnected';
            } else {
                badgeClass = 'bg-warning';
                icon = 'bx-error-circle';
                text = 'Error';
            }
            
            // Update system status badge
            $('#system_email_status').removeClass('bg-secondary bg-success bg-danger bg-warning')
                .addClass(badgeClass)
                .html(`<i class="bx ${icon}"></i> ${text}`);
            
            // Update any other email status badges
            if ($('#email_status_badge').length) {
                $('#email_status_badge').removeClass('bg-secondary bg-success bg-danger bg-warning')
                    .addClass(badgeClass)
                    .html(`<i class="bx ${icon}"></i> ${text}`);
            }
        },
        error: function(xhr, status, error) {
            console.error('Email status check error:', error);
            $('#system_email_status').removeClass('bg-secondary bg-success bg-warning')
                .addClass('bg-danger')
                .html('<i class="bx bx-x-circle"></i> Error');
            
            if ($('#email_status_badge').length) {
                $('#email_status_badge').removeClass('bg-secondary bg-success bg-warning')
                    .addClass('bg-danger')
                    .html('<i class="bx bx-x-circle"></i> Error');
            }
        }
    });
}

// Check SMS connection status
function checkSMSStatus() {
    $.ajax({
        url: '{{ route('admin.settings.communication.check-sms') }}',
        method: 'GET',
        timeout: 10000,
        success: function(response) {
            const status = response.status || 'error';
            const message = response.message || 'Unknown status';
            let badgeClass = 'bg-secondary';
            let icon = 'bx-x-circle';
            let text = 'Unknown';
            
            if (status === 'connected') {
                badgeClass = 'bg-success';
                icon = 'bx-check-circle';
                text = 'Connected';
            } else if (status === 'disconnected') {
                badgeClass = 'bg-danger';
                icon = 'bx-x-circle';
                text = 'Disconnected';
            } else {
                badgeClass = 'bg-warning';
                icon = 'bx-error-circle';
                text = 'Error';
            }
            
            // Update system status badge
            $('#system_sms_status').removeClass('bg-secondary bg-success bg-danger bg-warning')
                .addClass(badgeClass)
                .html(`<i class="bx ${icon}"></i> ${text}`);
            
            // Update any other SMS status badges
            if ($('#sms_status_badge').length) {
                $('#sms_status_badge').removeClass('bg-secondary bg-success bg-danger bg-warning')
                    .addClass(badgeClass)
                    .html(`<i class="bx ${icon}"></i> ${text}`);
            }
        },
        error: function(xhr, status, error) {
            console.error('SMS status check error:', error);
            $('#system_sms_status').removeClass('bg-secondary bg-success bg-warning')
                .addClass('bg-danger')
                .html('<i class="bx bx-x-circle"></i> Error');
            
            if ($('#sms_status_badge').length) {
                $('#sms_status_badge').removeClass('bg-secondary bg-success bg-warning')
                    .addClass('bg-danger')
                    .html('<i class="bx bx-x-circle"></i> Error');
            }
        }
    });
}

// Save SMS and Email settings separately
function saveCommunicationSettings() {
    const smsData = {
        sms_username: $('#sms_username').val(),
        sms_password: $('#sms_password').val(),
        sms_from: $('#sms_from').val(),
        sms_url: $('#sms_url').val(),
    };
    
    const emailData = {
        mail_mailer: $('#mail_mailer').val(),
        mail_host: $('#mail_host').val(),
        mail_port: $('#mail_port').val(),
        mail_username: $('#mail_username').val(),
        mail_password: $('#mail_password').val(),
        mail_encryption: $('#mail_encryption').val(),
        mail_from_address: $('#mail_from_address').val(),
        mail_from_name: $('#mail_from_name').val(),
    };
    
    const allData = { ...smsData, ...emailData };
    
    Swal.fire({
        title: 'Saving Communication Settings',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route('admin.settings.communication.update') }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: allData,
        success: function(response) {
            if(response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Communication settings updated successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Recheck connection status after saving
                    checkEmailStatus();
                    checkSMSStatus();
                });
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error updating communication settings';
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonText: 'OK'
            });
        }
    });
}

// Notification Providers Management
let currentProviderType = null;
let editingProviderId = null;

// Ensure functions are defined before they're used
// This prevents "function is not defined" errors
if (typeof window.testProvider === 'undefined') {
    window.testProvider = function() { console.error('testProvider not defined yet'); };
}
if (typeof window.editProvider === 'undefined') {
    window.editProvider = function() { console.error('editProvider not defined yet'); };
}
if (typeof window.deleteProvider === 'undefined') {
    window.deleteProvider = function() { console.error('deleteProvider not defined yet'); };
}
if (typeof window.setPrimaryProvider === 'undefined') {
    window.setPrimaryProvider = function() { console.error('setPrimaryProvider not defined yet'); };
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function loadNotificationProviders() {
    const tbody = document.getElementById('providersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    fetch('{{ route('admin.settings.notification-providers') }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        // Always try to parse JSON, even if status is not OK
        return res.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // If JSON parsing fails, create error object
                throw new Error('Invalid JSON response: ' + text.substring(0, 200));
            }
            
            // If HTTP status is not OK, throw error with message from response
            if (!res.ok) {
                const errorMsg = data.message || data.error || ('HTTP ' + res.status + ': ' + res.statusText);
                throw new Error(errorMsg);
            }
            
            return data;
        });
    })
    .then(data => {
        if (!data.success) {
            const errorMsg = data.message || data.error || 'Failed to load providers';
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4"><i class="bx bx-error-circle"></i> <strong>Error:</strong> ' + escapeHtml(errorMsg) + '</td></tr>';
            if (document.getElementById('legacyConfigSection')) {
                document.getElementById('legacyConfigSection').style.display = 'block';
            }
            console.error('Provider loading error:', data);
            return;
        }
        
        if (!data.providers || data.providers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No providers configured. Click "Add Email Provider" or "Add SMS Provider" to create one.</td></tr>';
            if (document.getElementById('legacyConfigSection')) {
                document.getElementById('legacyConfigSection').style.display = 'block';
            }
            return;
        }
        
        if (document.getElementById('legacyConfigSection')) {
            document.getElementById('legacyConfigSection').style.display = 'none';
        }
        
        tbody.innerHTML = data.providers.map((provider, index) => {
            const statusBadge = provider.is_active 
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';
            
            const primaryBadge = provider.is_primary 
                ? '<span class="badge bg-primary"><i class="bx bx-star"></i> Primary</span>'
                : '<button type="button" class="btn btn-sm btn-outline-primary" data-action="set-primary" data-provider-id="' + provider.id + '">Set Primary</button>';
            
            const connectionStatus = provider.status === 'connected'
                ? '<span class="badge bg-success"><i class="bx bx-check-circle"></i> Connected</span>'
                : provider.status === 'disconnected'
                ? '<span class="badge bg-danger"><i class="bx bx-x-circle"></i> Disconnected</span>'
                : '<span class="badge bg-warning"><i class="bx bx-error-circle"></i> Error</span>';
            
            const lastTest = provider.last_tested_at 
                ? '<small class="text-muted">' + provider.last_tested_at + '<br>' + 
                  (provider.last_test_status ? '<span class="text-success">✓ Success</span>' : '<span class="text-danger">✗ Failed</span>') + 
                  '</small>'
                : '<small class="text-muted">Never tested</small>';
            
            const typeBadge = provider.type === 'email'
                ? '<span class="badge bg-primary"><i class="bx bx-envelope"></i> Email</span>'
                : '<span class="badge bg-success"><i class="bx bx-message"></i> SMS</span>';
            
            return `
                <tr class="${provider.is_primary ? 'table-primary' : ''}">
                    <td>${index + 1}</td>
                    <td><strong>${escapeHtml(provider.name)}</strong></td>
                    <td>${typeBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${primaryBadge}</td>
                    <td><span class="badge bg-info">${provider.priority}</span></td>
                    <td>${connectionStatus}</td>
                    <td>${lastTest}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group" aria-label="Provider actions">
                            <button type="button" class="btn btn-sm btn-info" 
                                    data-action="test-provider" 
                                    data-provider-id="${provider.id}" 
                                    data-provider-type="${provider.type}"
                                    title="Test Provider">
                                <i class="bx bx-send"></i> Test
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-action="edit-provider" 
                                    data-provider-id="${provider.id}"
                                    title="Edit Provider">
                                <i class="bx bx-edit"></i> Edit
                            </button>
                            ${!provider.is_primary ? '<button type="button" class="btn btn-sm btn-danger" data-action="delete-provider" data-provider-id="' + provider.id + '" data-provider-name="' + escapeHtml(provider.name).replace(/"/g, '&quot;') + '" title="Delete Provider"><i class="bx bx-trash"></i> Delete</button>' : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    })
    .catch(err => {
        console.error('Error loading providers:', err);
        const errorMsg = err.message || err.toString() || 'Error loading providers';
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4"><i class="bx bx-error-circle"></i> <strong>Error:</strong> ' + escapeHtml(errorMsg) + '<br><small class="text-muted">Check browser console (F12) for more details</small></td></tr>';
        if (document.getElementById('legacyConfigSection')) {
            document.getElementById('legacyConfigSection').style.display = 'block';
        }
    });
}

function openProviderModal(type, providerId = null) {
    currentProviderType = type;
    editingProviderId = providerId;
    
    const title = providerId ? 'Edit ' + type.toUpperCase() + ' Provider' : 'Add New ' + type.toUpperCase() + ' Provider';
    
    // Build form HTML based on type
    let formHtml = `
        <div class="mb-3">
            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="provider_name" placeholder="e.g., Primary SMTP, Backup SMS Gateway" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="provider_description" rows="2" placeholder="Optional description"></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Priority (0-100)</label>
                <input type="number" class="form-control" id="provider_priority" min="0" max="100" value="0">
                <small class="text-muted">Higher priority providers are preferred</small>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="provider_is_active" checked>
                    <label class="form-check-label" for="provider_is_active">Active</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="provider_is_primary">
                    <label class="form-check-label" for="provider_is_primary">Set as Primary</label>
                </div>
            </div>
        </div>
    `;
    
    if (type === 'email') {
        formHtml += `
            <hr><h6>Email Configuration</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mailer Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="provider_mailer_type" required>
                        <option value="smtp">SMTP</option>
                        <option value="sendmail">Sendmail</option>
                        <option value="mailgun">Mailgun</option>
                        <option value="ses">Amazon SES</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="provider_mail_host" placeholder="smtp.gmail.com" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="provider_mail_port" placeholder="587" min="1" max="65535" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Encryption <span class="text-danger">*</span></label>
                    <select class="form-control" id="provider_mail_encryption" required>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" class="form-control" id="provider_mail_username" placeholder="your-email@gmail.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Password` + (providerId ? ' <span class="text-muted">(Leave blank to keep current)</span>' : '') + `</label>
                    <input type="password" class="form-control" id="provider_mail_password" placeholder="` + (providerId ? 'Leave blank to keep current password' : 'Enter SMTP password') + `">
                    ` + (providerId ? '<small class="text-muted">Only enter if you want to change the password</small>' : '') + `
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="provider_mail_from_address" placeholder="noreply@example.com" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="provider_mail_from_name" placeholder="OfisiLink" required>
                </div>
            </div>
        `;
    } else {
        formHtml += `
            <hr><h6>SMS Configuration</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMS Gateway Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="provider_sms_username" placeholder="Enter SMS gateway username" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMS Gateway Password` + (providerId ? ' <span class="text-muted">(Leave blank to keep current)</span>' : ' <span class="text-danger">*</span>') + `</label>
                    <input type="password" class="form-control" id="provider_sms_password" placeholder="` + (providerId ? 'Leave blank to keep current password' : 'Enter SMS gateway password') + `"` + (providerId ? '' : ' required') + `>
                    ` + (providerId ? '<small class="text-muted">Only enter if you want to change the password</small>' : '') + `
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMS Sender Name/From <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="provider_sms_from" placeholder="OfisiLink" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMS Gateway API URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="provider_sms_url" placeholder="https://messaging-service.co.tz/link/sms/v1/text/single" required>
                </div>
            </div>
        `;
    }
    
    Swal.fire({
        title: title,
        html: formHtml,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: providerId ? '<i class="bx bx-save"></i> Update Provider' : '<i class="bx bx-plus"></i> Create Provider',
        cancelButtonText: 'Cancel',
        confirmButtonColor: providerId ? '#ffc107' : '#198754',
        allowOutsideClick: false,
        allowEscapeKey: true,
        customClass: {
            popup: 'swal2-popup-provider',
            container: 'swal2-container-provider'
        },
        didOpen: () => {
            // Ensure modal appears above all elements
            const swalContainer = document.querySelector('.swal2-container');
            if (swalContainer) {
                swalContainer.style.zIndex = '99999';
            }
            const swalPopup = document.querySelector('.swal2-popup');
            if (swalPopup) {
                swalPopup.style.zIndex = '100000';
            }
            
            if (providerId) {
                // Load provider data for editing (ONLY loads, does NOT save)
                loadProviderData(providerId);
            }
        },
        preConfirm: () => {
            // Only called when user clicks Save/Update button
            // Validate form first
            if (!validateProviderForm()) {
                return false;
            }
            
            // Show loading state
            Swal.showLoading();
            
            // Save provider data
            return saveProvider();
        },
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading()
    });
}

function loadProviderData(providerId) {
    // This function ONLY loads data into the form fields
    // It does NOT save anything - saving only happens when user clicks Save/Update button
    
    fetch('{{ route('admin.settings.notification-providers') }}', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        return res.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
            
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Failed to load provider');
            }
            
            return data;
        });
    })
    .then(data => {
        if (data.success && data.providers && data.providers.length > 0) {
            const provider = data.providers.find(p => p.id == providerId);
            if (provider) {
                // Wait for form elements to be available, then populate them
                setTimeout(() => {
                    // Basic fields
                    const nameEl = document.getElementById('provider_name');
                    const descEl = document.getElementById('provider_description');
                    const priorityEl = document.getElementById('provider_priority');
                    const activeEl = document.getElementById('provider_is_active');
                    const primaryEl = document.getElementById('provider_is_primary');
                    
                    if (nameEl) nameEl.value = provider.name || '';
                    if (descEl) descEl.value = provider.description || '';
                    if (priorityEl) priorityEl.value = provider.priority || 0;
                    if (activeEl) activeEl.checked = provider.is_active || false;
                    if (primaryEl) primaryEl.checked = provider.is_primary || false;
                    
                    if (provider.type === 'email') {
                        // Email-specific fields
                        const mailerTypeEl = document.getElementById('provider_mailer_type');
                        const mailHostEl = document.getElementById('provider_mail_host');
                        const mailPortEl = document.getElementById('provider_mail_port');
                        const mailEncryptionEl = document.getElementById('provider_mail_encryption');
                        const mailUsernameEl = document.getElementById('provider_mail_username');
                        const mailFromAddressEl = document.getElementById('provider_mail_from_address');
                        const mailFromNameEl = document.getElementById('provider_mail_from_name');
                        
                        if (mailerTypeEl) mailerTypeEl.value = provider.mailer_type || 'smtp';
                        if (mailHostEl) mailHostEl.value = provider.mail_host || '';
                        if (mailPortEl) mailPortEl.value = provider.mail_port || '';
                        if (mailEncryptionEl) mailEncryptionEl.value = provider.mail_encryption || 'tls';
                        if (mailUsernameEl) mailUsernameEl.value = provider.mail_username || '';
                        if (mailFromAddressEl) mailFromAddressEl.value = provider.mail_from_address || '';
                        if (mailFromNameEl) mailFromNameEl.value = provider.mail_from_name || '';
                        
                        // Note: Password field is intentionally NOT populated for security
                        // User must re-enter password if they want to change it
                    } else {
                        // SMS-specific fields
                        const smsUsernameEl = document.getElementById('provider_sms_username');
                        const smsFromEl = document.getElementById('provider_sms_from');
                        const smsUrlEl = document.getElementById('provider_sms_url');
                        
                        if (smsUsernameEl) smsUsernameEl.value = provider.sms_username || '';
                        if (smsFromEl) smsFromEl.value = provider.sms_from || '';
                        if (smsUrlEl) smsUrlEl.value = provider.sms_url || '';
                        
                        // Note: Password field is intentionally NOT populated for security
                        // User must re-enter password if they want to change it
                    }
                }, 150);
            }
        }
    })
    .catch(err => {
        console.error('Error loading provider data:', err);
        Swal.showValidationMessage('Failed to load provider data: ' + err.message);
    });
}

window.editProvider = function(providerId) {
    // Show loading
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route('admin.settings.notification-providers') }}', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        return res.text().then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
            
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Failed to load provider');
            }
            
            return data;
        });
    })
    .then(data => {
        Swal.close();
        if (data.success && data.providers && data.providers.length > 0) {
            const provider = data.providers.find(p => p.id == providerId);
            if (provider) {
                openProviderModal(provider.type, providerId);
            } else {
                Swal.fire('Error', 'Provider not found', 'error');
            }
        } else {
            Swal.fire('Error', data.message || 'Failed to load provider data', 'error');
        }
    })
    .catch(err => {
        Swal.close();
        console.error('Error loading provider:', err);
        Swal.fire('Error', err.message || 'Failed to load provider', 'error');
    });
}

// Validate provider form before saving
function validateProviderForm() {
    const name = document.getElementById('provider_name');
    if (!name || !name.value.trim()) {
        Swal.showValidationMessage('Provider name is required');
        return false;
    }
    
    if (currentProviderType === 'email') {
        const mailHost = document.getElementById('provider_mail_host');
        const mailPort = document.getElementById('provider_mail_port');
        const mailFrom = document.getElementById('provider_mail_from_address');
        
        if (!mailHost || !mailHost.value.trim()) {
            Swal.showValidationMessage('SMTP Host is required');
            return false;
        }
        if (!mailPort || !mailPort.value) {
            Swal.showValidationMessage('SMTP Port is required');
            return false;
        }
        if (!mailFrom || !mailFrom.value.trim()) {
            Swal.showValidationMessage('From Email Address is required');
            return false;
        }
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(mailFrom.value.trim())) {
            Swal.showValidationMessage('Please enter a valid email address');
            return false;
        }
    } else {
        const smsUsername = document.getElementById('provider_sms_username');
        const smsFrom = document.getElementById('provider_sms_from');
        const smsUrl = document.getElementById('provider_sms_url');
        const smsPassword = document.getElementById('provider_sms_password');
        
        if (!smsUsername || !smsUsername.value.trim()) {
            Swal.showValidationMessage('SMS Username is required');
            return false;
        }
        // Password is only required when creating new provider, optional when editing
        if (!editingProviderId && (!smsPassword || !smsPassword.value.trim())) {
            Swal.showValidationMessage('SMS Password is required for new providers');
            return false;
        }
        if (!smsFrom || !smsFrom.value.trim()) {
            Swal.showValidationMessage('SMS From is required');
            return false;
        }
        if (!smsUrl || !smsUrl.value.trim()) {
            Swal.showValidationMessage('SMS URL is required');
            return false;
        }
        // Validate URL format
        try {
            new URL(smsUrl.value.trim());
        } catch (e) {
            Swal.showValidationMessage('Please enter a valid SMS URL');
            return false;
        }
    }
    
    return true;
}

function saveProvider() {
    // This function is ONLY called when user clicks Save/Update button
    // It is NOT called when data is loaded
    
    const data = {
        name: document.getElementById('provider_name').value.trim(),
        type: currentProviderType,
        description: document.getElementById('provider_description').value.trim(),
        priority: parseInt(document.getElementById('provider_priority').value) || 0,
        is_active: document.getElementById('provider_is_active').checked,
        is_primary: document.getElementById('provider_is_primary').checked,
    };
    
    if (currentProviderType === 'email') {
        data.mailer_type = document.getElementById('provider_mailer_type').value;
        data.mail_host = document.getElementById('provider_mail_host').value.trim();
        data.mail_port = parseInt(document.getElementById('provider_mail_port').value) || 587;
        data.mail_encryption = document.getElementById('provider_mail_encryption').value;
        data.mail_username = document.getElementById('provider_mail_username').value.trim();
        
        // Only include password if it's been changed (not empty)
        const mailPassword = document.getElementById('provider_mail_password').value;
        if (mailPassword && mailPassword.trim() !== '') {
            data.mail_password = mailPassword; // Don't trim password
        }
        // If editing and password is empty, don't send it (will keep existing password)
        
        data.mail_from_address = document.getElementById('provider_mail_from_address').value.trim();
        data.mail_from_name = document.getElementById('provider_mail_from_name').value.trim();
    } else {
        data.sms_username = document.getElementById('provider_sms_username').value.trim();
        
        // Only include password if it's been changed (not empty)
        const smsPassword = document.getElementById('provider_sms_password').value;
        if (smsPassword && smsPassword.trim() !== '') {
            data.sms_password = smsPassword; // Don't trim password
        }
        // If editing and password is empty, don't send it (will keep existing password)
        
        data.sms_from = document.getElementById('provider_sms_from').value.trim();
        data.sms_url = document.getElementById('provider_sms_url').value.trim();
    }
    
    const url = editingProviderId 
        ? '{{ route('admin.settings.notification-providers.update', ['provider' => ':id']) }}'.replace(':id', editingProviderId)
        : '{{ route('admin.settings.notification-providers.store') }}';
    
    const method = editingProviderId ? 'PUT' : 'POST';
    
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => {
        return res.text().then(text => {
            let responseData;
            try {
                responseData = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
            
            if (!res.ok) {
                throw new Error(responseData.message || responseData.error || 'Failed to save provider');
            }
            
            return responseData;
        });
    })
    .then(responseData => {
        if (responseData.success) {
            Swal.fire({
                icon: 'success',
                title: editingProviderId ? 'Updated!' : 'Created!',
                text: responseData.message || (editingProviderId ? 'Provider updated successfully' : 'Provider created successfully'),
                confirmButtonText: 'OK'
            }).then(() => {
                loadNotificationProviders();
            });
            return true;
        } else {
            Swal.showValidationMessage(responseData.message || responseData.error || 'Error saving provider');
            return false;
        }
    })
    .catch(err => {
        console.error('Save provider error:', err);
        Swal.showValidationMessage('Error: ' + (err.message || 'Network error occurred'));
        return false;
    });
}

window.deleteProvider = function(providerId, providerName) {
    Swal.fire({
        title: 'Delete Provider?',
        html: `Are you sure you want to delete <strong>${escapeHtml(providerName)}</strong>?<br><br><span class="text-danger">This action cannot be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-trash"></i> Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('{{ route('admin.settings.notification-providers.delete', ['provider' => ':id']) }}'.replace(':id', providerId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                return res.text().then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                    
                    if (!res.ok) {
                        throw new Error(data.message || data.error || 'Failed to delete provider');
                    }
                    
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Provider deleted successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        loadNotificationProviders();
                    });
                } else {
                    Swal.fire('Error', data.message || data.error || 'Failed to delete provider', 'error');
                }
            })
            .catch(err => {
                console.error('Delete provider error:', err);
                Swal.fire('Error', err.message || 'Network error occurred', 'error');
            });
        }
    });
}

window.setPrimaryProvider = function(providerId) {
    Swal.fire({
        title: 'Set as Primary?',
        html: 'This provider will be used for all system notifications.<br><br><small class="text-muted">Other providers of the same type will be unset as primary.</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-star"></i> Yes, Set as Primary',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Setting Primary...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('{{ route('admin.settings.notification-providers.set-primary', ['provider' => ':id']) }}'.replace(':id', providerId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                return res.text().then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                    
                    if (!res.ok) {
                        throw new Error(data.message || data.error || 'Failed to set primary provider');
                    }
                    
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Provider set as primary successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        loadNotificationProviders();
                    });
                } else {
                    Swal.fire('Error', data.message || data.error || 'Failed to set primary provider', 'error');
                }
            })
            .catch(err => {
                console.error('Set primary provider error:', err);
                Swal.fire('Error', err.message || 'Network error occurred', 'error');
            });
        }
    });
}

// Make functions globally accessible
window.testProvider = function(providerId, type) {
    const inputLabel = type === 'email' ? 'Email Address' : 'Phone Number';
    const inputType = type === 'email' ? 'email' : 'text';
    const inputPlaceholder = type === 'email' ? 'test@example.com' : '255712345678';
    const inputHelp = type === 'email' 
        ? 'Enter the email address to send a test email to' 
        : 'Enter phone number (format: 255712345678)';
    
    Swal.fire({
        title: 'Test ' + type.toUpperCase() + ' Provider',
        html: `
            <div class="mb-3">
                <label class="form-label"><strong>${inputLabel}</strong></label>
                <input type="${inputType}" id="test_input" class="form-control" placeholder="${inputPlaceholder}" required>
                <small class="text-muted">${inputHelp}</small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-send"></i> Send Test',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
            const testValue = document.getElementById('test_input').value.trim();
            if (!testValue) {
                Swal.showValidationMessage('Please enter ' + inputLabel.toLowerCase());
                return false;
            }
            
            // Validate email format if email type
            if (type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(testValue)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            // Validate phone format if SMS type
            if (type === 'sms') {
                const cleanPhone = testValue.replace(/[^0-9]/g, '');
                if (cleanPhone.length < 9) {
                    Swal.showValidationMessage('Please enter a valid phone number (at least 9 digits)');
                    return false;
                }
            }
            
            const data = type === 'email' ? { test_email: testValue } : { test_phone: testValue };
            
            return fetch('{{ route('admin.settings.notification-providers.test', ['provider' => ':id']) }}'.replace(':id', providerId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => {
                // Try to parse JSON even if status is not OK
                return res.text().then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server: ' + text.substring(0, 200));
                    }
                    
                    if (!res.ok) {
                        throw new Error(data.message || data.error || ('HTTP ' + res.status + ': ' + res.statusText));
                    }
                    
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Test Sent Successfully!',
                        text: data.message || 'Test ' + type.toUpperCase() + ' sent successfully. Please check your ' + (type === 'email' ? 'inbox' : 'phone') + '.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        loadNotificationProviders();
                    });
                    return true;
                } else {
                    Swal.showValidationMessage(data.message || data.error || 'Test failed. Please check the configuration.');
                    return false;
                }
            })
            .catch(err => {
                console.error('Test provider error:', err);
                Swal.showValidationMessage('Error: ' + (err.message || err.toString() || 'Network error occurred'));
                return false;
            });
        }
    });
}

// Event delegation for action buttons - This ensures buttons work even if dynamically loaded
$(document).on('click', '[data-action="test-provider"]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const providerId = $btn.data('provider-id');
    const providerType = $btn.data('provider-type');
    
    console.log('Test provider button clicked:', { providerId, providerType });
    
    if (!providerId || !providerType) {
        console.error('Missing provider ID or type');
        Swal.fire('Error', 'Missing provider information', 'error');
        return false;
    }
    
    if (typeof window.testProvider === 'function') {
        try {
            window.testProvider(providerId, providerType);
        } catch (err) {
            console.error('Error calling testProvider:', err);
            Swal.fire('Error', 'Failed to test provider: ' + err.message, 'error');
        }
    } else {
        console.error('testProvider function not defined');
        Swal.fire('Error', 'Test function not available. Please refresh the page.', 'error');
    }
    return false;
});

$(document).on('click', '[data-action="edit-provider"]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const providerId = $btn.data('provider-id');
    
    console.log('Edit provider button clicked:', { providerId });
    
    if (!providerId) {
        console.error('Missing provider ID');
        Swal.fire('Error', 'Missing provider information', 'error');
        return false;
    }
    
    if (typeof window.editProvider === 'function') {
        try {
            window.editProvider(providerId);
        } catch (err) {
            console.error('Error calling editProvider:', err);
            Swal.fire('Error', 'Failed to edit provider: ' + err.message, 'error');
        }
    } else {
        console.error('editProvider function not defined');
        Swal.fire('Error', 'Edit function not available. Please refresh the page.', 'error');
    }
    return false;
});

$(document).on('click', '[data-action="delete-provider"]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const providerId = $btn.data('provider-id');
    const providerName = $btn.data('provider-name') || 'this provider';
    
    console.log('Delete provider button clicked:', { providerId, providerName });
    
    if (!providerId) {
        console.error('Missing provider ID');
        Swal.fire('Error', 'Missing provider information', 'error');
        return false;
    }
    
    if (typeof window.deleteProvider === 'function') {
        try {
            window.deleteProvider(providerId, providerName);
        } catch (err) {
            console.error('Error calling deleteProvider:', err);
            Swal.fire('Error', 'Failed to delete provider: ' + err.message, 'error');
        }
    } else {
        console.error('deleteProvider function not defined');
        Swal.fire('Error', 'Delete function not available. Please refresh the page.', 'error');
    }
    return false;
});

$(document).on('click', '[data-action="set-primary"]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const providerId = $btn.data('provider-id');
    
    console.log('Set primary button clicked:', { providerId });
    
    if (!providerId) {
        console.error('Missing provider ID');
        Swal.fire('Error', 'Missing provider information', 'error');
        return false;
    }
    
    if (typeof window.setPrimaryProvider === 'function') {
        try {
            window.setPrimaryProvider(providerId);
        } catch (err) {
            console.error('Error calling setPrimaryProvider:', err);
            Swal.fire('Error', 'Failed to set primary provider: ' + err.message, 'error');
        }
    } else {
        console.error('setPrimaryProvider function not defined');
        Swal.fire('Error', 'Set Primary function not available. Please refresh the page.', 'error');
    }
    return false;
});

// Load providers on page load
$(document).ready(function() {
    loadNotificationProviders();
});

</script>
@endpush

