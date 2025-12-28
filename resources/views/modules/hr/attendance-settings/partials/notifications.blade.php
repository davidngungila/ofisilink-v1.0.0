<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-bell text-primary"></i> Notification Settings
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Email Notifications</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Admin Email</label>
                    <input type="email" class="form-control" id="adminEmail" placeholder="admin@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Frequency</label>
                    <select class="form-select" id="emailFrequency">
                        <option value="realtime">Real-time</option>
                        <option value="hourly">Hourly</option>
                        <option value="daily">Daily Summary</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Notification Events</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyDeviceOffline">
                    <label class="form-check-label" for="notifyDeviceOffline">Device goes offline</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifySyncFailed">
                    <label class="form-check-label" for="notifySyncFailed">Sync failure</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyLateArrival">
                    <label class="form-check-label" for="notifyLateArrival">Late arrival</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="notifyAbsenteeism">
                    <label class="form-check-label" for="notifyAbsenteeism">Absenteeism</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <button type="button" class="btn btn-primary" onclick="saveNotificationSettings()">
            <i class="bx bx-save me-1"></i> Save Notification Settings
        </button>
    </div>
</div>

<script>
function loadNotificationSettings() {
    // Load notification settings
}

function saveNotificationSettings() {
    const settings = {
        admin_email: document.getElementById('adminEmail').value,
        email_frequency: document.getElementById('emailFrequency').value,
        notify_device_offline: document.getElementById('notifyDeviceOffline').checked,
        notify_device_sync_failed: document.getElementById('notifySyncFailed').checked,
        notify_late_arrival: document.getElementById('notifyLateArrival').checked,
        notify_absenteeism: document.getElementById('notifyAbsenteeism').checked,
    };

    fetch('{{ route("modules.hr.attendance.settings") }}/notifications', {
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
            Swal.fire('Success!', 'Notification settings saved', 'success');
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    });
}
</script>









