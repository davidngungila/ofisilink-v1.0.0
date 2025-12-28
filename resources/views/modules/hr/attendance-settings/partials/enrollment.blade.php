<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-user-plus text-primary me-2"></i>User Enrollment to Device
        </h5>
        <p class="text-muted">Register system employees to ZKTeco biometric device. <strong>Employee ID is automatically used as Enroll ID.</strong></p>
    </div>
</div>

<!-- Device Connection Settings -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-wifi me-1"></i>Device Connection Settings</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Device IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="enrollDeviceIp" value="{{ $deviceIp ?? config('zkteco.ip', '192.168.100.108') }}" placeholder="192.168.100.108">
                        <small class="text-muted">ZKTeco device IP address</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Port <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="enrollDevicePort" value="{{ $devicePort ?? config('zkteco.port', 4370) }}" placeholder="4370" min="1" max="65535">
                        <small class="text-muted">Default: 4370</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Comm Key</label>
                        <input type="number" class="form-control" id="enrollDevicePassword" value="{{ $deviceCommKey ?? config('zkteco.password', 0) }}" placeholder="0" min="0" max="65535">
                        <small class="text-muted">Default: 0</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-info" onclick="testEnrollmentConnection()">
                                <i class="bx bx-wifi me-1"></i>Test Connection
                            </button>
                        </div>
                    </div>
                </div>
                <div id="enrollmentConnectionResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Employees</h6>
                        <h3 class="mb-0" id="statTotalEmployees">0</h3>
                    </div>
                    <div class="text-primary">
                        <i class="bx bx-users fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Enrolled</h6>
                        <h3 class="mb-0" id="statEnrolled">0</h3>
                    </div>
                    <div class="text-success">
                        <i class="bx bx-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Not Enrolled</h6>
                        <h3 class="mb-0" id="statNotEnrolled">0</h3>
                    </div>
                    <div class="text-warning">
                        <i class="bx bx-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">No Enroll ID</h6>
                        <h3 class="mb-0" id="statNoEnrollId">0</h3>
                    </div>
                    <div class="text-info">
                        <i class="bx bx-info-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Registration Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bx bx-user-plus me-1"></i>Register Users to Device
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <p class="mb-2 text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>First, test the device connection above.</strong> Then select employees and register them to the device.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" onclick="enrollSelectedEmployees()" id="enrollSelectedBtn">
                                <i class="bx bx-check-square me-1"></i>Register Selected
                            </button>
                            <button type="button" class="btn btn-success" onclick="enrollAllEmployees()" id="enrollAllBtn">
                                <i class="bx bx-user-plus me-1"></i>Register All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employees List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bx bx-users me-1"></i>Employees List
                </h6>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" onclick="syncAttendanceFromDevices()" title="Sync attendance from all devices">
                        <i class="bx bx-sync me-1"></i>Sync Attendance
                    </button>
                    <button type="button" class="btn btn-info btn-sm" onclick="syncFromDevice()" title="Sync users from device">
                        <i class="bx bx-download me-1"></i>Sync Users
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshEmployeesList()">
                        <i class="bx bx-refresh me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="employeeSearch" placeholder="Search by name or employee ID..." onkeyup="filterEmployees()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="employeeDepartmentFilter" onchange="filterEmployees()">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="employeeEnrollmentFilter" onchange="filterEmployees()">
                            <option value="">All Status</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="not_enrolled">Not Enrolled</option>
                            <option value="no_enroll_id">No Enroll ID</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="refreshEmployeesList()">
                            <i class="bx bx-refresh me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Employees Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="employeesEnrollmentTable">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAllEmployees" onchange="toggleSelectAll()">
                                </th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Enroll ID</th>
                                <th>Status</th>
                                <th>Last Enrolled</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employeesEnrollmentTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading employees...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
try {
    let employeesData = @json($employees ?? []);
    if (typeof employeesData === 'undefined' || employeesData === null) {
        employeesData = [];
    }
} catch(e) {
    var employeesData = [];
    console.error('Error loading employees data:', e);
}

// Load employees on tab show
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentTab = document.querySelector('a[href="#enrollment"]');
    if (enrollmentTab) {
        enrollmentTab.addEventListener('shown.bs.tab', function() {
            loadEmployeesList();
        });
    }
});

function loadEmployeesList() {
    const tbody = document.getElementById('employeesEnrollmentTableBody');
    if (!tbody) return;

    if (!employeesData || employeesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No employees found</p></td></tr>';
        updateStatistics();
        return;
    }

    let html = '';
    employeesData.forEach(employee => {
        const enrollId = employee.enroll_id || '';
        const isEnrolled = employee.registered_on_device || false;
        const hasEnrollId = enrollId !== '';
        const department = employee.primary_department?.name || employee.employee?.department?.name || 'N/A';
        const employeeId = employee.employee?.employee_id || employee.id;

        let statusBadge = '';
        if (isEnrolled) {
            statusBadge = '<span class="badge bg-success">Enrolled</span>';
        } else if (!hasEnrollId) {
            statusBadge = '<span class="badge bg-warning">No Enroll ID</span>';
        } else {
            statusBadge = '<span class="badge bg-secondary">Not Enrolled</span>';
        }

        const lastEnrolled = employee.device_registered_at ? new Date(employee.device_registered_at).toLocaleDateString() : '-';
        
        html += '<tr data-employee-id="' + employee.id + '" data-enroll-id="' + enrollId + '" data-enrolled="' + isEnrolled + '">';
        html += '<td><input type="checkbox" class="employee-checkbox" value="' + employee.id + '"' + (!hasEnrollId ? ' disabled' : '') + '></td>';
        html += '<td><strong>' + (employeeId || 'N/A') + '</strong></td>';
        html += '<td>' + (employee.name || 'N/A') + '</td>';
        html += '<td><span class="badge bg-light text-dark">' + department + '</span></td>';
        html += '<td>';
        if (hasEnrollId) {
            html += '<code>' + enrollId + '</code>';
        } else {
            html += '<span class="text-muted">-</span>';
            html += '<button class="btn btn-sm btn-outline-primary ms-2" onclick="generateEnrollId(' + employee.id + ')" title="Generate Enroll ID"><i class="bx bx-plus"></i></button>';
        }
        html += '</td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td><small class="text-muted">' + lastEnrolled + '</small></td>';
        html += '<td>';
        if (hasEnrollId) {
            if (isEnrolled) {
                html += '<button class="btn btn-sm btn-success" disabled><i class="bx bx-check me-1"></i>Registered</button>';
                html += '<button class="btn btn-sm btn-outline-warning ms-1" onclick="reEnrollEmployee(' + employee.id + ')" title="Re-register to device"><i class="bx bx-refresh"></i> Re-register</button>';
                html += '<button class="btn btn-sm btn-outline-danger ms-1" onclick="unregisterEmployee(' + employee.id + ')" title="Unregister from device"><i class="bx bx-user-minus"></i> Unregister</button>';
            } else {
                html += '<button class="btn btn-sm btn-primary" onclick="enrollSingleEmployee(' + employee.id + ')" title="Register this employee to device"><i class="bx bx-user-plus me-1"></i>Register</button>';
            }
        } else {
            html += '<button class="btn btn-sm btn-outline-secondary" disabled><i class="bx bx-x me-1"></i>No Enroll ID</button>';
        }
        html += '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
    updateStatistics();
}

function updateStatistics() {
    if (!employeesData || employeesData.length === 0) {
        document.getElementById('statTotalEmployees').textContent = '0';
        document.getElementById('statEnrolled').textContent = '0';
        document.getElementById('statNotEnrolled').textContent = '0';
        document.getElementById('statNoEnrollId').textContent = '0';
        return;
    }
    
    const total = employeesData.length;
    const enrolled = employeesData.filter(e => e.registered_on_device).length;
    const notEnrolled = employeesData.filter(e => e.enroll_id && !e.registered_on_device).length;
    const noEnrollId = employeesData.filter(e => !e.enroll_id).length;
    
    document.getElementById('statTotalEmployees').textContent = total;
    document.getElementById('statEnrolled').textContent = enrolled;
    document.getElementById('statNotEnrolled').textContent = notEnrolled;
    document.getElementById('statNoEnrollId').textContent = noEnrollId;
}

function filterEmployees() {
    const search = document.getElementById('employeeSearch')?.value.toLowerCase() || '';
    const deptFilter = document.getElementById('employeeDepartmentFilter')?.value || '';
    const statusFilter = document.getElementById('employeeEnrollmentFilter')?.value || '';
    const rows = document.querySelectorAll('#employeesEnrollmentTableBody tr[data-employee-id]');

    rows.forEach(row => {
        const employeeId = row.getAttribute('data-employee-id');
        const employee = employeesData.find(e => e.id == employeeId);
        if (!employee) return;

        const name = (employee.name || '').toLowerCase();
        const empId = (employee.employee?.employee_id || '').toLowerCase();
        const department = employee.primary_department?.id || employee.employee?.department_id || '';
        const enrollId = employee.enroll_id || '';
        const isEnrolled = employee.registered_on_device || false;

        let show = true;

        // Search filter
        if (search && !name.includes(search) && !empId.includes(search) && !enrollId.includes(search)) {
            show = false;
        }

        // Department filter
        if (deptFilter && department != deptFilter) {
            show = false;
        }

        // Status filter
        if (statusFilter) {
            if (statusFilter === 'enrolled' && !isEnrolled) show = false;
            if (statusFilter === 'not_enrolled' && (isEnrolled || !enrollId)) show = false;
            if (statusFilter === 'no_enroll_id' && enrollId) show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllEmployees');
    const checkboxes = document.querySelectorAll('.employee-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function testEnrollmentConnection() {
    const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
    const portInput = document.getElementById('enrollDevicePort')?.value;
    const passwordInput = document.getElementById('enrollDevicePassword')?.value;
    const resultDiv = document.getElementById('enrollmentConnectionResult');

    if (!ip) {
        showToast('Please enter device IP address', 'warning');
        return;
    }

    // Convert to proper types
    const port = portInput ? parseInt(portInput) : 4370;
    const password = passwordInput ? parseInt(passwordInput) : 0;

    // Validate IP format
    const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
    if (!ipPattern.test(ip)) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bx bx-x-circle me-2"></i>Invalid IP address format</div>';
        return;
    }

    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bx bx-loader bx-spin me-2"></i>Testing connection...</div>';

    const testConnectionUrl = '{{ route("zkteco.test-connection") }}';
    const csrfToken = '{{ csrf_token() }}';
    
    fetch(testConnectionUrl, {
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
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Status: ' + response.status);
            });
        }
        
        // Parse JSON
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
            const deviceInfo = data.device_info ? (data.device_info.firmware_version || data.device_info.device_name || 'Connected') : '';
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="bx bx-check-circle me-2"></i>Connection successful!' + (deviceInfo ? '<br><small>Device: ' + deviceInfo + '</small>' : '') + '</div>';
        } else {
            const errorMsg = data.message || 'Unknown error';
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bx bx-x-circle me-2"></i>Connection failed: ' + errorMsg + '</div>';
        }
    })
    .catch(error => {
        console.error('Connection test error:', error);
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bx bx-x-circle me-2"></i>Error: ' + error.message + '</div>';
    });
}

function generateEnrollId(userId) {
    if (!confirm('Generate Enroll ID for this employee? It will use Employee ID as Enroll ID.')) {
        return;
    }

    const generateUrl = '/api/v1/users/' + userId + '/generate-enroll-id';
    const csrfToken = '{{ csrf_token() }}';
    
    fetch(generateUrl, {
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
        if (data.success) {
            showToast('Enroll ID generated successfully', 'success');
            refreshEmployeesList();
        } else {
            showToast(data.message || 'Failed to generate Enroll ID', 'error');
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
    });
}

function enrollSingleEmployee(userId) {
    try {
        const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
        const portInput = document.getElementById('enrollDevicePort')?.value;
        const passwordInput = document.getElementById('enrollDevicePassword')?.value;

        if (!ip) {
            showToast('Please enter device IP address and test connection first', 'warning');
            return;
        }

        // Convert to proper types
        const port = portInput ? parseInt(portInput) : 4370;
        const password = passwordInput ? parseInt(passwordInput) : 0;

        // Find employee data
        const employee = employeesData.find(e => e.id == userId);
        if (!employee) {
            showToast('Employee not found', 'error');
            return;
        }

        if (!employee.enroll_id) {
            showToast('Employee does not have an Enroll ID. Please generate one first.', 'warning');
            return;
        }

        if (!confirm('Register "' + (employee.name || 'Employee') + '" (Enroll ID: ' + employee.enroll_id + ') to the device?\n\nThis will send the employee data to the ZKTeco device.')) {
            return;
        }

        // Show splash screen
        if (typeof showRegistrationSplash === 'function') {
            showRegistrationSplash(employee.name || 'Employee', employee.enroll_id);
        }
        
        // Use the new enrollment endpoint
        const enrollUrl = '{{ route("attendance-settings.users.enroll") }}';
        const csrfToken = '{{ csrf_token() }}';
        
        // Disable button
        const btn = event?.target?.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Registering...';
        }
        
        // Simulate progress updates
        if (typeof updateSplashStep === 'function') {
            setTimeout(function() { updateSplashStep(1, 'Connecting to device...'); }, 100);
            setTimeout(function() { updateSplashStep(2, 'Enabling device...'); }, 500);
            setTimeout(function() { updateSplashStep(3, 'Checking existing user...'); }, 1000);
            setTimeout(function() { updateSplashStep(4, 'Registering user...'); }, 1500);
        }
        
        // Set up timeout (60 seconds max)
        const timeoutDuration = 60000;
        let requestAborted = false;
        
        // Create AbortController for timeout handling
        const controller = new AbortController();
        const timeout = setTimeout(function() {
            controller.abort();
            requestAborted = true;
        }, timeoutDuration);
        
        // Enroll user directly with IP/port
        fetch(enrollUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                user_id: userId,
                ip_address: ip,
                port: port,
                comm_key: password,
                fingers: []
            }),
            signal: controller.signal
        })
        .then(function(response) {
            clearTimeout(timeout);
            
            if (typeof updateSplashStep === 'function') {
                updateSplashStep(5, 'Verifying registration...');
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(function(text) {
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response. Status: ' + response.status);
                });
            }
            
            return response.json();
        })
        .then(function(data) {
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response from server');
            }
            
            if (data.success) {
                // Mark all steps as completed
                if (typeof updateSplashStep === 'function') {
                    for (let i = 1; i <= 5; i++) {
                        const step = document.getElementById('step' + i);
                        if (step) {
                            step.classList.add('completed');
                            step.classList.remove('active');
                        }
                    }
                    const progressBar = document.getElementById('splashProgressBar');
                    if (progressBar) progressBar.style.width = '100%';
                }
                
                // Update splash with success message
                const title = document.getElementById('splashTitle');
                const message = document.getElementById('splashMessage');
                const splashIcon = document.querySelector('#registrationSplash .splash-icon');
                
                if (title) title.textContent = 'Registration Successful!';
                if (message) message.textContent = 'Employee "' + (employee.name || 'Employee') + '" has been successfully registered to the device.';
                if (splashIcon) {
                    splashIcon.innerHTML = '<i class="bx bx-check-circle text-success"></i>';
                    splashIcon.className = 'splash-icon text-success';
                }
                
                setTimeout(function() {
                    if (typeof hideRegistrationSplash === 'function') {
                        hideRegistrationSplash();
                    }
                    showToast('✓ Employee registered successfully to device!', 'success');
                    refreshEmployeesList();
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to register employee');
            }
        })
        .catch(function(error) {
            clearTimeout(timeout);
            
            console.error('Enrollment error:', error);
            
            // Determine error message
            let errorMessage = 'Failed to register employee to device.';
            if (error.name === 'AbortError' || requestAborted) {
                errorMessage = 'Request timeout. The device may be slow to respond. Please check device connection and try again.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            // Update splash with error message
            const title = document.getElementById('splashTitle');
            const message = document.getElementById('splashMessage');
            const splashIcon = document.querySelector('#registrationSplash .splash-icon');
            
            if (title) title.textContent = 'Registration Failed';
            if (message) message.textContent = errorMessage;
            if (splashIcon) {
                splashIcon.innerHTML = '<i class="bx bx-x-circle text-danger"></i>';
                splashIcon.className = 'splash-icon text-danger';
            }
            
            // Add close button to splash screen
            const splashContent = document.querySelector('#registrationSplash .splash-content');
            if (splashContent && !splashContent.querySelector('.splash-close-btn')) {
                const closeBtn = document.createElement('button');
                closeBtn.className = 'btn btn-danger mt-3 splash-close-btn';
                closeBtn.innerHTML = '<i class="bx bx-x me-1"></i>Close';
                closeBtn.onclick = function() {
                    if (typeof hideRegistrationSplash === 'function') {
                        hideRegistrationSplash();
                    }
                };
                splashContent.appendChild(closeBtn);
            }
            
            // Auto-hide after 5 seconds
            const hideDelay = requestAborted ? 5000 : 3000;
            setTimeout(function() {
                if (typeof hideRegistrationSplash === 'function') {
                    hideRegistrationSplash();
                }
                showToast('Registration failed: ' + errorMessage, 'error');
            }, hideDelay);
        })
        .finally(function() {
            clearTimeout(timeout);
            
            // Re-enable button
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-user-plus me-1"></i>Register';
            }
        });
    } catch (err) {
        console.error('Function error:', err);
        showToast('Error: ' + err.message, 'error');
    }
}

function enrollSelectedEmployees() {
    const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
    const portInput = document.getElementById('enrollDevicePort')?.value;
    const passwordInput = document.getElementById('enrollDevicePassword')?.value;

    if (!ip) {
        showToast('Please enter device IP address and test connection first', 'warning');
        return;
    }

    // Convert to proper types
    const port = portInput ? parseInt(portInput) : 4370;
    const password = passwordInput ? parseInt(passwordInput) : 0;

    const selected = Array.from(document.querySelectorAll('.employee-checkbox:checked')).map(cb => parseInt(cb.value));
    
    if (selected.length === 0) {
        showToast('Please select at least one employee to register', 'warning');
        return;
    }

    const selectedEmployees = employeesData.filter(e => selected.includes(e.id) && e.enroll_id && !e.registered_on_device);

    if (selectedEmployees.length === 0) {
        showToast('Selected employees are already enrolled or missing Enroll ID.', 'info');
        return;
    }

    const employeeNames = selectedEmployees.slice(0, 5).map(e => e.name).join(', ');
    const moreCount = selectedEmployees.length > 5 ? ' and ' + (selectedEmployees.length - 5) + ' more' : '';
    
    if (!confirm('Register ' + selectedEmployees.length + ' employee(s) to the device?\n\n' + employeeNames + moreCount + '\n\nThis will send all employee data to the ZKTeco device.')) {
        return;
    }

    // Disable buttons
    const enrollSelectedBtn = document.getElementById('enrollSelectedBtn');
    const enrollAllBtn = document.getElementById('enrollAllBtn');
    if (enrollSelectedBtn) enrollSelectedBtn.disabled = true;
    if (enrollAllBtn) enrollAllBtn.disabled = true;

    showToast('Registering ' + selectedEmployees.length + ' employee(s) to device...', 'info');
    
    enrollEmployeesBatch(selectedEmployees.map(e => e.id), ip, port, password);
}

function enrollAllEmployees() {
    const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
    const portInput = document.getElementById('enrollDevicePort')?.value;
    const passwordInput = document.getElementById('enrollDevicePassword')?.value;

    if (!ip) {
        showToast('Please enter device IP address and test connection first', 'warning');
        return;
    }

    // Convert to proper types
    const port = portInput ? parseInt(portInput) : 4370;
    const password = passwordInput ? parseInt(passwordInput) : 0;

    const allEmployees = employeesData.filter(e => e.enroll_id && !e.registered_on_device);

    if (allEmployees.length === 0) {
        showToast('No employees to register. All employees are already enrolled or missing Enroll ID.', 'info');
        return;
    }

    if (!confirm('Register ALL ' + allEmployees.length + ' employee(s) to the device?\n\nThis will send all employee data to the ZKTeco device.\n\nThis may take a few minutes.')) {
        return;
    }

    // Disable buttons
    const enrollSelectedBtn = document.getElementById('enrollSelectedBtn');
    const enrollAllBtn = document.getElementById('enrollAllBtn');
    if (enrollSelectedBtn) enrollSelectedBtn.disabled = true;
    if (enrollAllBtn) enrollAllBtn.disabled = true;

    showToast('Registering ' + allEmployees.length + ' employee(s) to device...', 'info');
    
    enrollEmployeesBatch(allEmployees.map(e => e.id), ip, port, password);
}

function enrollEmployeesBatch(userIds, ip, port, password) {
    const progressDiv = document.createElement('div');
    progressDiv.id = 'enrollmentProgress';
    progressDiv.className = 'alert alert-info mt-3';
    progressDiv.innerHTML = '<i class="bx bx-loader bx-spin me-2"></i>Registering ' + userIds.length + ' employee(s) to device... Please wait.';
    const connectionResult = document.getElementById('enrollmentConnectionResult');
    if (connectionResult) {
        connectionResult.appendChild(progressDiv);
    }
    
    showToast('Registering ' + userIds.length + ' employee(s) to device... This may take a moment.', 'info');

    // Register users one by one using direct device connection
    // This ensures each user is registered directly to the device
    let registered = 0;
    let failed = 0;
    let skipped = 0;
    let currentIndex = 0;
    
    const csrfToken = '{{ csrf_token() }}';
    
    function registerNextUser() {
        if (currentIndex >= userIds.length) {
            // All done
            const progressDiv = document.getElementById('enrollmentProgress');
            if (progressDiv) progressDiv.remove();
            
            // Re-enable buttons
            const enrollSelectedBtn = document.getElementById('enrollSelectedBtn');
            const enrollAllBtn = document.getElementById('enrollAllBtn');
            if (enrollSelectedBtn) enrollSelectedBtn.disabled = false;
            if (enrollAllBtn) enrollAllBtn.disabled = false;
            
            let message = '✓ Successfully registered ' + registered + ' employee(s) to device!';
            if (failed > 0) message += '\n' + failed + ' failed';
            if (skipped > 0) message += '\n' + skipped + ' skipped';
            
            showToast(message, 'success');
            
            // Refresh the list after a short delay
            setTimeout(() => {
                refreshEmployeesList();
            }, 1500);
            return;
        }
        
        const userId = userIds[currentIndex];
        currentIndex++;
        
        // Update progress
        const progressDiv = document.getElementById('enrollmentProgress');
        if (progressDiv) {
            progressDiv.innerHTML = '<i class="bx bx-loader bx-spin me-2"></i>Registering ' + currentIndex + ' of ' + userIds.length + ' employee(s)... Please wait.';
        }
        
        // Register this user using the enrollment endpoint
        // First get device ID
        const enrollUrl = '{{ route("attendance-settings.users.enroll") }}';
        
        // Enroll user directly with IP/port
        return fetch(enrollUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                user_id: userId,
                ip_address: ip,
                port: port,
                comm_key: password,
                fingers: []
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
                registered++;
            } else {
                failed++;
                console.error('Failed to register user ' + userId + ':', data.message);
            }
            
            // Small delay before next registration to avoid overwhelming device
            setTimeout(() => {
                registerNextUser();
            }, 500);
        })
        .catch(error => {
            failed++;
            console.error('Error registering user ' + userId + ':', error);
            
            // Continue with next user even if this one failed
            setTimeout(() => {
                registerNextUser();
            }, 500);
        });
    }
    
    // Start registering users
    registerNextUser();
}

function unregisterEmployee(userId) {
    const ip = document.getElementById('enrollDeviceIp')?.value?.trim();
    const portInput = document.getElementById('enrollDevicePort')?.value;
    const passwordInput = document.getElementById('enrollDevicePassword')?.value;

    if (!ip) {
        showToast('Please enter device IP address and test connection first', 'warning');
        return;
    }

    // Convert to proper types
    const port = portInput ? parseInt(portInput) : 4370;
    const password = passwordInput ? parseInt(passwordInput) : 0;

    // Find employee data
    const employee = employeesData.find(e => e.id == userId);
    if (!employee) {
        showToast('Employee not found', 'error');
        return;
    }

    if (!employee.enroll_id) {
        showToast('Employee does not have an Enroll ID.', 'warning');
        return;
    }

    if (!confirm('Unregister "' + (employee.name || 'Employee') + '" (Enroll ID: ' + employee.enroll_id + ') from the device?\n\nThis will remove the employee from the ZKTeco device.')) {
        return;
    }

    // Show loading state
    const unregisterUrl = '/zkteco/users/' + userId + '/unregister';
    const csrfToken = '{{ csrf_token() }}';
    
    // Disable button and show loading
    const btn = event?.target?.closest('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Unregistering...';
    }
    
    showToast('Unregistering employee from device...', 'info');
    
    fetch(unregisterUrl, {
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
            showToast('✓ Employee unregistered successfully from device!', 'success');
            // Refresh the list after a short delay
            setTimeout(() => {
                refreshEmployeesList();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to unregister employee', 'error');
        }
    })
    .catch(error => {
        console.error('Unregister error:', error);
        showToast('Unregistration failed: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable button
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-user-minus"></i> Unregister';
        }
    });
}

function syncAttendanceFromDevices() {
    if (!confirm('Sync attendance records from all active devices? This will capture check-in and check-out times from devices and save to database.')) {
        return;
    }
    
    const syncUrl = '{{ route("attendance-settings.devices.sync-all") }}';
    const csrfToken = '{{ csrf_token() }}';
    
    // Show loading
    showToast('Syncing attendance from devices... This may take a moment.', 'info');
    
    // Disable button
    const btn = event?.target?.closest('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Syncing...';
    }
    
    fetch(syncUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const message = '✓ Synced ' + (data.total_records || 0) + ' attendance record(s) from ' + (data.synced_devices || 0) + ' device(s)';
            showToast(message, 'success');
        } else {
            showToast(data.message || 'Failed to sync attendance', 'error');
        }
    })
    .catch(error => {
        console.error('Sync attendance error:', error);
        showToast('Error syncing attendance: ' + error.message, 'error');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-sync me-1"></i>Sync Attendance';
        }
    });
}

function refreshEmployeesList() {
    // Reload employees from API
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
            showToast('Employees list refreshed', 'success');
        } else {
            showToast('Failed to refresh employees list', 'error');
        }
    })
    .catch(error => {
        console.error('Error refreshing employees:', error);
        // Fallback to reload page
        location.reload();
    });
}

function showToast(message, type = 'info') {
    // Use your existing toast notification system
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

// Registration Splash Screen Functions
function showRegistrationSplash(employeeName, enrollId) {
    const splash = document.getElementById('registrationSplash');
    const title = document.getElementById('splashTitle');
    const message = document.getElementById('splashMessage');
    const progressBar = document.getElementById('splashProgressBar');
    
    if (splash) {
        if (title) title.textContent = 'Registering User to Device';
        if (message) message.textContent = 'Registering "' + employeeName + '" (Enroll ID: ' + enrollId + ') to device...';
        if (progressBar) progressBar.style.width = '0%';
        
        // Reset all steps
        for (let i = 1; i <= 5; i++) {
            const step = document.getElementById('step' + i);
            if (step) {
                step.classList.remove('active', 'completed');
            }
        }
        
        // Reset icon
        const splashIcon = document.querySelector('#registrationSplash .splash-icon');
        if (splashIcon) {
            splashIcon.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
            splashIcon.className = 'splash-icon text-primary';
        }
        
        // Remove any existing close button
        const splashContent = document.querySelector('#registrationSplash .splash-content');
        const existingCloseBtn = splashContent?.querySelector('.splash-close-btn');
        if (existingCloseBtn) {
            existingCloseBtn.remove();
        }
        
        splash.classList.add('show');
    }
}

function hideRegistrationSplash() {
    const splash = document.getElementById('registrationSplash');
    if (splash) {
        splash.classList.remove('show');
    }
}

function updateSplashStep(stepNumber, stepText) {
    const step = document.getElementById('step' + stepNumber);
    if (step) {
        step.textContent = stepText;
        step.classList.add('active');
        step.classList.remove('completed');
        
        // Mark previous steps as completed
        for (let i = 1; i < stepNumber; i++) {
            const prevStep = document.getElementById('step' + i);
            if (prevStep) {
                prevStep.classList.remove('active');
                prevStep.classList.add('completed');
            }
        }
        
        // Update progress bar
        const progressBar = document.getElementById('splashProgressBar');
        if (progressBar) {
            const progress = (stepNumber / 5) * 100;
            progressBar.style.width = progress + '%';
        }
    }
}
</script>

