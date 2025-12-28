<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-cog text-primary"></i> General Attendance Settings
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bx bx-info-circle me-2"></i> System Configuration
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Default Attendance Method</label>
                    <select class="form-select" id="defaultAttendanceMethod">
                        <option value="biometric">Biometric (Fingerprint/Face)</option>
                        <option value="mobile_app">Mobile App</option>
                        <option value="manual">Manual Entry</option>
                        <option value="rfid">RFID Card</option>
                    </select>
                    <small class="text-muted">Default method for recording attendance</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Time Zone</label>
                    <select class="form-select" id="timezone">
                        <option value="Africa/Dar_es_Salaam" selected>Africa/Dar_es_Salaam (EAT)</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enableAutoApproval" checked>
                        <label class="form-check-label" for="enableAutoApproval">
                            Auto-approve verified biometric entries
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="requirePhotoForManual">
                        <label class="form-check-label" for="requirePhotoForManual">
                            Require photo for manual attendance
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="requireLocationForMobile" checked>
                        <label class="form-check-label" for="requireLocationForMobile">
                            Require GPS location for mobile attendance
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bx bx-time me-2"></i> Time Settings
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Late Tolerance (minutes)</label>
                    <input type="number" class="form-control" id="lateTolerance" value="15" min="0" max="120">
                    <small class="text-muted">Minutes after scheduled time before marking as late</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Early Leave Tolerance (minutes)</label>
                    <input type="number" class="form-control" id="earlyLeaveTolerance" value="15" min="0" max="120">
                    <small class="text-muted">Minutes before scheduled end time allowed for early leave</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Overtime Threshold (minutes)</label>
                    <input type="number" class="form-control" id="overtimeThreshold" value="30" min="0" max="480">
                    <small class="text-muted">Minutes beyond scheduled hours to count as overtime</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Break Duration (minutes)</label>
                    <input type="number" class="form-control" id="defaultBreakDuration" value="60" min="0" max="480">
                    <small class="text-muted">Default break duration in minutes</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bx bx-data me-2"></i> Data Retention
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Attendance Data Retention (days)</label>
                            <input type="number" class="form-control" id="dataRetentionDays" value="365" min="30" max="3650">
                            <small class="text-muted">How long to keep attendance records before archiving</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Archive Old Records</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoArchive" checked>
                                <label class="form-check-label" for="autoArchive">
                                    Automatically archive records older than retention period
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <button type="button" class="btn btn-primary" onclick="saveGeneralSettings()">
            <i class="bx bx-save me-1"></i> Save General Settings
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="resetGeneralSettings()">
            <i class="bx bx-reset me-1"></i> Reset to Defaults
        </button>
    </div>
</div>

<script>
function loadGeneralSettings() {
    // Load settings from server
    fetch('{{ route("admin.settings.communication.data") }}')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Populate form fields
                // Implementation here
            }
        });
}

function saveGeneralSettings() {
    const settings = {
        default_attendance_method: document.getElementById('defaultAttendanceMethod').value,
        timezone: document.getElementById('timezone').value,
        enable_auto_approval: document.getElementById('enableAutoApproval').checked,
        require_photo_for_manual: document.getElementById('requirePhotoForManual').checked,
        require_location_for_mobile: document.getElementById('requireLocationForMobile').checked,
        late_tolerance: document.getElementById('lateTolerance').value,
        early_leave_tolerance: document.getElementById('earlyLeaveTolerance').value,
        overtime_threshold: document.getElementById('overtimeThreshold').value,
        default_break_duration: document.getElementById('defaultBreakDuration').value,
        data_retention_days: document.getElementById('dataRetentionDays').value,
        auto_archive: document.getElementById('autoArchive').checked,
    };

    // Save via API
    fetch('{{ route("modules.hr.attendance.settings") }}/general', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'General settings saved successfully', 'success');
        } else {
            Swal.fire('Error!', data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error!', 'Failed to save settings', 'error');
    });
}

function resetGeneralSettings() {
    Swal.fire({
        title: 'Reset to Defaults?',
        text: 'This will reset all general settings to their default values.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset form fields to defaults
            document.getElementById('defaultAttendanceMethod').value = 'biometric';
            document.getElementById('timezone').value = 'Africa/Dar_es_Salaam';
            document.getElementById('enableAutoApproval').checked = true;
            document.getElementById('requirePhotoForManual').checked = false;
            document.getElementById('requireLocationForMobile').checked = true;
            document.getElementById('lateTolerance').value = 15;
            document.getElementById('earlyLeaveTolerance').value = 15;
            document.getElementById('overtimeThreshold').value = 30;
            document.getElementById('defaultBreakDuration').value = 60;
            document.getElementById('dataRetentionDays').value = 365;
            document.getElementById('autoArchive').checked = true;
            
            Swal.fire('Reset!', 'Settings reset to defaults', 'success');
        }
    });
}
</script>









