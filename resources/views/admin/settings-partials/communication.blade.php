<!-- Connection Status Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bx-message-rounded-dots fs-1 text-primary"></i>
                </div>
                <h5 class="mb-2">SMS Service</h5>
                <div id="smsStatus" class="status-badge status-checking mb-2">
                    <i class="bx bx-loader bx-spin me-1"></i>Checking...
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="checkSMSStatus()">
                    <i class="bx bx-refresh me-1"></i>Refresh Status
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bx-envelope fs-1 text-success"></i>
                </div>
                <h5 class="mb-2">Email Service</h5>
                <div id="emailStatus" class="status-badge status-checking mb-2">
                    <i class="bx bx-loader bx-spin me-1"></i>Checking...
                </div>
                <button class="btn btn-sm btn-outline-success" onclick="checkEmailStatus()">
                    <i class="bx bx-refresh me-1"></i>Refresh Status
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0 text-white">
            <i class="bx bx-envelope"></i> Communication Settings (SMS & Email)
        </h5>
        <p class="text-white-50 mb-0 small">Configure SMS and Email credentials. Settings are stored in database, not hardcoded.</p>
    </div>
    <div class="card-body">
        <form id="communicationSettingsForm">
            @csrf
            
            <!-- SMS Settings -->
            <div class="mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bx bx-message fs-4 text-primary me-2"></i>
                    <h6 class="mb-0">SMS Gateway Configuration</h6>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMS Username</label>
                        <input type="text" class="form-control" id="sms_username" name="sms_username" 
                               placeholder="Enter SMS gateway username">
                        <small class="text-muted">SMS gateway API username</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMS Password</label>
                        <input type="password" class="form-control" id="sms_password" name="sms_password" 
                               placeholder="Enter SMS gateway password">
                        <small class="text-muted">SMS gateway API password</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMS From (Sender Name)</label>
                        <input type="text" class="form-control" id="sms_from" name="sms_from" 
                               placeholder="e.g., OfisiLink">
                        <small class="text-muted">Name displayed as sender</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMS API URL</label>
                        <input type="url" class="form-control" id="sms_url" name="sms_url" 
                               placeholder="https://messaging-service.co.tz/link/sms/v1/text/single">
                        <small class="text-muted">SMS gateway API endpoint URL</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Test SMS</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="testSmsPhone" 
                                   placeholder="255712345678" pattern="^255[0-9]{9}$">
                            <button type="button" class="btn btn-outline-primary" onclick="testSMS()">
                                <i class="bx bx-send"></i> Send Test SMS
                            </button>
                        </div>
                        <small class="text-muted">Format: 255XXXXXXXXX (12 digits)</small>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Email Settings -->
            <div class="mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bx bx-mail-send fs-4 text-success me-2"></i>
                    <h6 class="mb-0">Email (SMTP) Configuration</h6>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mailer Type</label>
                        <select class="form-select" id="mail_mailer" name="mail_mailer">
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="mailgun">Mailgun</option>
                            <option value="ses">Amazon SES</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" id="mail_host" name="mail_host" 
                               placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" class="form-control" id="mail_port" name="mail_port" 
                               placeholder="587" min="1" max="65535">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Encryption</label>
                        <select class="form-select" id="mail_encryption" name="mail_encryption">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username</label>
                        <input type="email" class="form-control" id="mail_username" name="mail_username" 
                               placeholder="your-email@gmail.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="mail_password" name="mail_password" 
                               placeholder="Enter SMTP password or app password">
                        <small class="text-muted">For Gmail, use App Password</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Email Address</label>
                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                               placeholder="noreply@ofisilink.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                               placeholder="OfisiLink System">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Test Email</label>
                        <div class="input-group">
                            <input type="email" class="form-control" id="testEmailAddress" 
                                   placeholder="test@example.com">
                            <button type="button" class="btn btn-outline-primary" onclick="testEmail()">
                                <i class="bx bx-send"></i> Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" onclick="saveCommunicationSettings()">
                    <i class="bx bx-save"></i> Save Communication Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-block;
    }
    .status-connected {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    .status-disconnected {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    .status-checking {
        background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Load communication settings on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for all scripts to load
    setTimeout(function() {
        loadCommunicationSettings();
        checkSMSStatus();
        checkEmailStatus();
    }, 100);
});

function loadCommunicationSettings() {
    fetch('{{ route("admin.settings.communication.data") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            return res.json();
        })
        .then(data => {
            if(data.success && data.settings) {
                const sms = data.settings.sms || {};
                const email = data.settings.email || {};
                
                // Populate SMS fields
                document.getElementById('sms_username').value = sms.username || '';
                document.getElementById('sms_password').value = sms.password || '';
                document.getElementById('sms_from').value = sms.from || '';
                document.getElementById('sms_url').value = sms.url || '';
                
                // Populate Email fields
                document.getElementById('mail_mailer').value = email.mailer || 'smtp';
                document.getElementById('mail_host').value = email.host || '';
                document.getElementById('mail_port').value = email.port || '';
                document.getElementById('mail_encryption').value = email.encryption || 'tls';
                document.getElementById('mail_username').value = email.username || '';
                document.getElementById('mail_password').value = email.password || '';
                document.getElementById('mail_from_address').value = email.from_address || '';
                document.getElementById('mail_from_name').value = email.from_name || '';
            }
        })
        .catch(err => {
            console.error('Error loading communication settings:', err);
            // Show user-friendly error
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Could not load communication settings. Please refresh the page.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
}

function saveCommunicationSettings() {
    const formData = {
        sms_username: document.getElementById('sms_username').value,
        sms_password: document.getElementById('sms_password').value,
        sms_from: document.getElementById('sms_from').value,
        sms_url: document.getElementById('sms_url').value,
        mail_mailer: document.getElementById('mail_mailer').value,
        mail_host: document.getElementById('mail_host').value,
        mail_port: parseInt(document.getElementById('mail_port').value),
        mail_username: document.getElementById('mail_username').value,
        mail_password: document.getElementById('mail_password').value,
        mail_encryption: document.getElementById('mail_encryption').value,
        mail_from_address: document.getElementById('mail_from_address').value,
        mail_from_name: document.getElementById('mail_from_name').value,
    };

    fetch('{{ route("admin.settings.communication.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Success', data.message || 'Communication settings saved successfully', 'success');
        } else {
            Swal.fire('Error', data.message || 'Failed to save communication settings', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
    });
}

function testSMS() {
    const phone = document.getElementById('testSmsPhone').value;
    if(!phone) {
        Swal.fire('Error', 'Please enter a phone number', 'error');
        return;
    }

    Swal.fire({
        title: 'Sending Test SMS...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("admin.settings.communication.test-sms") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Success', data.message || 'Test SMS sent successfully', 'success');
        } else {
            Swal.fire('Error', data.message || 'Failed to send test SMS', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
    });
}

function testEmail() {
    const email = document.getElementById('testEmailAddress').value;
    if(!email) {
        Swal.fire('Error', 'Please enter an email address', 'error');
        return;
    }

    Swal.fire({
        title: 'Sending Test Email...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("admin.settings.communication.test-email") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Success', data.message || 'Test email sent successfully', 'success');
            checkEmailStatus();
        } else {
            Swal.fire('Error', data.message || 'Failed to send test email', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
    });
}

function checkSMSStatus() {
    const statusEl = document.getElementById('smsStatus');
    if (!statusEl) return;
    
    statusEl.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Checking...';
    statusEl.className = 'status-badge status-checking mb-2';
    
    fetch('{{ route("admin.settings.communication.check-sms") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(data => {
            if(data.success && data.status === 'connected') {
                statusEl.innerHTML = '<i class="bx bx-check-circle me-1"></i>Connected';
                statusEl.className = 'status-badge status-connected mb-2';
            } else {
                statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Disconnected';
                statusEl.className = 'status-badge status-disconnected mb-2';
            }
        })
        .catch(err => {
            statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Error';
            statusEl.className = 'status-badge status-disconnected mb-2';
        });
}

function checkEmailStatus() {
    const statusEl = document.getElementById('emailStatus');
    if (!statusEl) return;
    
    statusEl.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Checking...';
    statusEl.className = 'status-badge status-checking mb-2';
    
    fetch('{{ route("admin.settings.communication.check-email") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(data => {
            if(data.success && data.status === 'connected') {
                statusEl.innerHTML = '<i class="bx bx-check-circle me-1"></i>Connected';
                statusEl.className = 'status-badge status-connected mb-2';
            } else {
                statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Disconnected';
                statusEl.className = 'status-badge status-disconnected mb-2';
            }
        })
        .catch(err => {
            statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Error';
            statusEl.className = 'status-badge status-disconnected mb-2';
        });
}
</script>
@endpush






