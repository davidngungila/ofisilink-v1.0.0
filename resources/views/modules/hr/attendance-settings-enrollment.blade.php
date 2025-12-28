@extends('layouts.app')

@section('title', 'User Enrollment to Device')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-user-plus"></i> User Enrollment to Device
                </h4>
                <p class="text-muted">Register system employees to ZKTeco biometric device. <strong>Employee ID is automatically used as Enroll ID.</strong></p>
            </div>
            <div>
                <div class="btn-group">
                    <a href="{{ route('zkteco.test') }}" class="btn btn-info">
                        <i class="bx bx-wifi me-1"></i> Test Connection
                    </a>
                    <a href="{{ route('zkteco.register') }}" class="btn btn-success">
                        <i class="bx bx-user-plus me-1"></i> Register User
                    </a>
                    <a href="{{ route('zkteco.retrieve') }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Retrieve Data
                    </a>
                    <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .border-left-primary { border-left: 4px solid #0d6efd; }
    .border-left-success { border-left: 4px solid #198754; }
    .border-left-warning { border-left: 4px solid #ffc107; }
    .border-left-info { border-left: 4px solid #0dcaf0; }
    
    /* Registration Splash Screen */
    #registrationSplash {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    #registrationSplash.show {
        display: flex;
    }
    
    .splash-content {
        background: white;
        border-radius: 12px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    
    .splash-icon {
        font-size: 64px;
        margin-bottom: 20px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    
    .splash-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .splash-message {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }
    
    .splash-progress {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .splash-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #0d6efd, #0dcaf0);
        width: 0%;
        transition: width 0.3s ease;
        animation: progress 2s infinite;
    }
    
    @keyframes progress {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(400%); }
    }
    
    .splash-steps {
        text-align: left;
        margin-top: 20px;
    }
    
    .splash-step {
        padding: 8px 0;
        color: #666;
        font-size: 14px;
    }
    
    .splash-step.active {
        color: #0d6efd;
        font-weight: bold;
    }
    
    .splash-step.completed {
        color: #198754;
    }
    
    .splash-step.completed:before {
        content: "✓ ";
        color: #198754;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @include('modules.hr.attendance-settings.partials.enrollment')
</div>

<!-- Registration Splash Screen -->
<div id="registrationSplash" onclick="if(event.target.id === 'registrationSplash') { if(typeof hideRegistrationSplash === 'function') hideRegistrationSplash(); }">
    <div class="splash-content" onclick="event.stopPropagation()">
        <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2" onclick="if(typeof hideRegistrationSplash === 'function') hideRegistrationSplash();" style="z-index: 10;" title="Close">
            <i class="bx bx-x"></i>
        </button>
        <div class="splash-icon text-primary">
            <i class="bx bx-loader-alt bx-spin"></i>
        </div>
        <div class="splash-title" id="splashTitle">Registering User to Device</div>
        <div class="splash-message" id="splashMessage">Please wait while we register the employee to the device...</div>
        <div class="splash-progress">
            <div class="splash-progress-bar" id="splashProgressBar"></div>
        </div>
        <div class="splash-steps" id="splashSteps">
            <div class="splash-step" id="step1">Connecting to device...</div>
            <div class="splash-step" id="step2">Enabling device...</div>
            <div class="splash-step" id="step3">Checking existing user...</div>
            <div class="splash-step" id="step4">Registering user...</div>
            <div class="splash-step" id="step5">Verifying registration...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Load employees immediately on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if employees data is available
    if (typeof employeesData === 'undefined' || !employeesData || employeesData.length === 0) {
        // Load from API
        loadEmployeesFromAPI();
    } else {
        loadEmployeesList();
    }
});

function loadEmployeesFromAPI() {
    const apiUrl = '/attendance-settings/employees/list';
    const csrfToken = '{{ csrf_token() }}';
    
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Expected JSON but got HTML');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.employees) {
            employeesData = data.employees;
            loadEmployeesList();
        } else {
            const tbody = document.getElementById('employeesEnrollmentTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No employees found</p></td></tr>';
            }
        }
    })
    .catch(error => {
        console.error('Error loading employees:', error);
        const tbody = document.getElementById('employeesEnrollmentTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bx bx-error-circle fs-1"></i><p class="mt-2">Error loading employees: ' + error.message + '</p></td></tr>';
        }
    });
}

function showToast(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}

function reEnrollEmployee(userId) {
    if (!confirm('Re-enroll this employee to the device? This will update the device with current information.')) {
        return;
    }
    enrollSingleEmployee(userId);
}

function syncFromDevice() {
    const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
    const portInput = document.getElementById('enrollDevicePort')?.value;
    const passwordInput = document.getElementById('enrollDevicePassword')?.value;

    if (!ip) {
        showToast('Please enter device IP address', 'warning');
        return;
    }

    // Convert to proper types
    const port = portInput ? parseInt(portInput) : 4370;
    const password = passwordInput ? parseInt(passwordInput) : 0;

    if (!confirm('Capture users from device? This will fetch all users registered on the device.')) {
        return;
    }

    // Use direct device connection endpoint (NOT external API)
    const captureUrl = '/zkteco/users/capture-from-device';
    const csrfToken = '{{ csrf_token() }}';
    
    showToast('Capturing users from device...', 'info');
    
    fetch(captureUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            ip: ip, 
            port: port, 
            password: password 
        })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Status: ' + response.status);
            });
        }
        return response.json().then(data => {
            if (!response.ok) {
                // Handle validation errors
                if (response.status === 422 && data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error('Validation error: ' + errorMessages);
                }
                throw new Error(data.message || 'HTTP error! status: ' + response.status);
            }
            return data;
        });
    })
    .then(data => {
        if (data.success) {
            const totalUsers = data.total || 0;
            const users = data.users || [];
            
            // Display captured users info
            console.log('Users captured from device:', users);
            
            showToast('Captured ' + totalUsers + ' user(s) from device', 'success');
            
            // Optionally update local employees if enroll_id matches
            if (users.length > 0 && typeof employeesData !== 'undefined') {
                updateEmployeesFromDeviceUsers(users);
            }
            
            refreshEmployeesList();
        } else {
            showToast(data.message || 'Failed to capture users from device', 'error');
        }
    })
    .catch(error => {
        console.error('Capture from device error:', error);
        showToast('Error: ' + error.message, 'error');
    });
}

function updateEmployeesFromDeviceUsers(deviceUsers) {
    // Match device users with local employees by enroll_id
    if (typeof employeesData === 'undefined' || !employeesData || employeesData.length === 0) return;
    
    let updated = 0;
    deviceUsers.forEach(deviceUser => {
        const employee = employeesData.find(e => e.enroll_id == deviceUser.uid);
        if (employee && !employee.registered_on_device) {
            employee.registered_on_device = true;
            updated++;
        }
    });
    
    if (updated > 0) {
        console.log('Updated ' + updated + ' employee(s) enrollment status based on device users');
    }
}
</script>
@endpush

