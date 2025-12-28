<!-- Company Info Modal -->
<div class="modal fade" id="companyInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="companyInfoForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Company Information</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ $settings->company_name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="company_registration_number" class="form-control" value="{{ $settings->company_registration_number }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax ID</label>
                            <input type="text" name="company_tax_id" class="form-control" value="{{ $settings->company_tax_id }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="company_email" class="form-control" value="{{ $settings->company_email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ $settings->company_phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" name="company_website" class="form-control" value="{{ $settings->company_website }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ $settings->company_address }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="company_city" class="form-control" value="{{ $settings->company_city }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State/Region</label>
                            <input type="text" name="company_state" class="form-control" value="{{ $settings->company_state }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="company_country" class="form-control" value="{{ $settings->company_country }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="company_postal_code" class="form-control" value="{{ $settings->company_postal_code }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Currency Modal -->
<div class="modal fade" id="currencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="currencyForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Currency & Regional Settings</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-control" required>
                                <option value="TZS" {{ $settings->currency == 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                <option value="USD" {{ $settings->currency == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ $settings->currency == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ $settings->currency == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ $settings->currency_symbol }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency Position</label>
                            <select name="currency_position" class="form-control">
                                <option value="prefix" {{ $settings->currency_position == 'prefix' ? 'selected' : '' }}>Prefix (e.g., TSh 1,000)</option>
                                <option value="suffix" {{ $settings->currency_position == 'suffix' ? 'selected' : '' }}>Suffix (e.g., 1,000 TSh)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Decimal Places</label>
                            <input type="number" name="decimal_places" class="form-control" value="{{ $settings->decimal_places }}" min="0" max="4">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timezone <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-control" required>
                                <option value="Africa/Dar_es_Salaam" {{ $settings->timezone == 'Africa/Dar_es_Salaam' ? 'selected' : '' }}>Africa/Dar es Salaam</option>
                                <option value="UTC" {{ $settings->timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Africa/Nairobi" {{ $settings->timezone == 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Format</label>
                            <select name="date_format" class="form-control">
                                <option value="Y-m-d" {{ $settings->date_format == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="d-m-Y" {{ $settings->date_format == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                <option value="m/d/Y" {{ $settings->date_format == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="d/m/Y" {{ $settings->date_format == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time Format</label>
                            <select name="time_format" class="form-control">
                                <option value="H:i:s" {{ $settings->time_format == 'H:i:s' ? 'selected' : '' }}>24 Hour (HH:MM:SS)</option>
                                <option value="h:i:s A" {{ $settings->time_format == 'h:i:s A' ? 'selected' : '' }}>12 Hour (HH:MM:SS AM/PM)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Business Modal -->
<div class="modal fade" id="businessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="businessForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Business & Payroll Settings</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Hours Start</label>
                            <input type="time" name="business_hours_start" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($settings->business_hours_start)->format('H:i') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Hours End</label>
                            <input type="time" name="business_hours_end" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($settings->business_hours_end)->format('H:i') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payroll Period (Days)</label>
                            <input type="number" name="payroll_period_days" class="form-control" 
                                   value="{{ $settings->payroll_period_days }}" min="1" max="365">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payroll Processing Day</label>
                            <input type="number" name="payroll_processing_day" class="form-control" 
                                   value="{{ $settings->payroll_processing_day }}" min="1" max="31">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Annual Leave (Days)</label>
                            <input type="number" name="default_annual_leave_days" class="form-control" 
                                   value="{{ $settings->default_annual_leave_days }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Sick Leave (Days)</label>
                            <input type="number" name="default_sick_leave_days" class="form-control" 
                                   value="{{ $settings->default_sick_leave_days }}" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logo Upload Modal -->
<div class="modal fade" id="logoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="logoForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Company Logo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*" required>
                        <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                    </div>
                    <div class="text-center">
                        <img id="logoPreview" src="" alt="Preview" class="img-fluid" style="max-height: 150px; display: none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Logo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form submissions
$('#companyInfoForm, #currencyForm, #businessForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    
    $.ajax({
        url: '{{ route("settings.update") }}',
        method: 'PUT',
        data: formData,
        success: function(response) {
            if(response.success) {
                $(this).closest('.modal').modal('hide');
                Swal.fire('Success', 'Settings updated successfully', 'success').then(() => location.reload());
            }
        }.bind(this),
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Error updating settings', 'error');
        }
    });
});

$('#logoForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: '{{ route("settings.upload-logo") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                $('#logoModal').modal('hide');
                Swal.fire('Success', 'Logo uploaded successfully', 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Error uploading logo', 'error');
        }
    });
});

// Logo preview
$('input[name="logo"]').on('change', function(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logoPreview').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
    }
});
</script>






