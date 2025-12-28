@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('breadcrumb')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c20000 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="text-white mb-1">
                                <i class="bx bx-edit-alt me-2"></i>Edit User Account
                            </h3>
                            <p class="text-white-50 mb-0">Update user credentials, contact information, roles, and status</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i> Back to Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Read-Only Information -->
        <div class="col-lg-5 mb-4">
            <!-- Personal Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bx bx-user text-primary me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Full Name</label>
                            <div class="form-control-plaintext fw-semibold">{{ $user->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Employee ID</label>
                            <div class="form-control-plaintext fw-semibold">
                                <span class="badge bg-primary">{{ $user->employee_id ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Date of Birth</label>
                            <div class="form-control-plaintext">{{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Gender</label>
                            <div class="form-control-plaintext">{{ $user->gender ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Nationality</label>
                            <div class="form-control-plaintext">{{ $user->nationality ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Marital Status</label>
                            <div class="form-control-plaintext">
                                @if($user->marital_status)
                                    <span class="badge bg-info">{{ ucfirst($user->marital_status) }}</span>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Address</label>
                            <div class="form-control-plaintext">{{ $user->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bx bx-briefcase text-success me-2"></i>Employment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Hire Date</label>
                            <div class="form-control-plaintext fw-semibold">
                                {{ $user->hire_date ? $user->hire_date->format('d M Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Primary Department</label>
                            <div class="form-control-plaintext">
                                @if($user->primaryDepartment)
                                    <span class="badge bg-success">{{ $user->primaryDepartment->name }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        @if($user->employee)
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Position</label>
                            <div class="form-control-plaintext">{{ $user->employee->position ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Employment Type</label>
                            <div class="form-control-plaintext">
                                @if($user->employee->employment_type)
                                    <span class="badge bg-warning text-dark">{{ ucfirst($user->employee->employment_type) }}</span>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Employment Status</label>
                            <div class="form-control-plaintext">
                                @if($user->employee->employment_status)
                                    <span class="badge bg-info">{{ ucfirst($user->employee->employment_status) }}</span>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bx bx-info-circle text-info me-2"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Account Created</label>
                            <div class="form-control-plaintext">{{ $user->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Last Updated</label>
                            <div class="form-control-plaintext">{{ $user->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Email Verified</label>
                            <div class="form-control-plaintext">
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle me-1"></i>{{ $user->email_verified_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">Not Verified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Editable Fields -->
        <div class="col-lg-7 mb-4">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')
                        
                <!-- Editable Information Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bx bx-edit text-danger me-2"></i>Editable Information
                        </h5>
                        <small class="text-muted">Only these fields can be modified</small>
                            </div>
                    <div class="card-body">
                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                Email Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-envelope text-primary"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="email"
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="user@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">This email will be used for login and notifications. Leave unchanged to keep current email.</small>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold">
                                Phone Number
                                @if(auth()->user()->id == $user->id && !auth()->user()->hasRole('System Admin'))
                                    <span class="badge bg-warning text-dark ms-2">OTP Required</span>
                                @endif
                            </label>
                            @if(!$user->phone && !$user->mobile)
                                <div class="alert alert-warning mb-3">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <small>No phone number registered. Please contact administrator.</small>
                                </div>
                            @endif
                            @if(auth()->user()->id == $user->id && !auth()->user()->hasRole('System Admin'))
                                <!-- Staff editing their own phone - OTP verification required -->
                                <div class="alert alert-info mb-3">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <small>You must verify your current phone number with OTP before changing it.</small>
                                </div>
                                <div id="phoneOtpSection" style="display: none;">
                                    <div class="card border-info mb-3">
                                        <div class="card-body">
                                            <label class="form-label">Enter OTP sent to your current phone</label>
                                            <div class="input-group mb-3">
                                                <input type="text" 
                                                       id="phoneOtpCode" 
                                                       class="form-control" 
                                                       placeholder="Enter 6-digit OTP"
                                                       maxlength="6">
                                                <button type="button" 
                                                        class="btn btn-info" 
                                                        id="verifyPhoneOtpBtn">
                                                    <i class="bx bx-check me-1"></i>Verify OTP
                                                </button>
                                            </div>
                                            <small class="text-muted">OTP is valid for 10 minutes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-light">
                                        <i class="bx bx-phone text-success"></i>
                                    </span>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="+255 XXX XXX XXX"
                                           readonly>
                                    <button type="button" 
                                            class="btn btn-warning" 
                                            id="sendPhoneOtpBtn">
                                        <i class="bx bx-send me-1"></i>Send OTP
                                    </button>
                                </div>
                                <input type="hidden" name="phone_otp_verified" id="phone_otp_verified" value="0">
                            @else
                                <!-- Admin editing other user's phone - no OTP required -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bx bx-phone text-success"></i>
                                    </span>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="+255 XXX XXX XXX">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                            @error('phone')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Reset Section -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-lock text-warning me-1"></i>Password Reset
                                <span class="badge bg-danger ms-2">Admin Only</span>
                            </label>
                            <div class="alert alert-warning d-flex align-items-start mb-3" role="alert">
                                <i class="bx bx-shield-quarter me-2 mt-1"></i>
                                <div>
                                    <strong>System Administrator Only:</strong>
                                    <small class="d-block mt-1">Only System Administrators can reset passwords. When a password is reset, SMS notifications with the automatically generated password will be sent to both the admin and the affected staff member.</small>
                                </div>
                            </div>
                            
                            @if(auth()->user() && auth()->user()->hasRole('System Admin'))
                            <div class="card border-warning mb-3">
                                <div class="card-body text-center">
                                    <h6 class="mb-3">
                                        <i class="bx bx-key me-2 text-warning"></i>Auto Generate Password
                                    </h6>
                                    <p class="text-muted mb-3">Click the button below to automatically generate a secure password. The password will be displayed immediately and SMS notifications will be sent to both admin and user right away.</p>
                                    <button type="button" 
                                            class="btn btn-warning btn-lg px-5" 
                                            id="resetPasswordBtn"
                                            onclick="generateAndShowPassword()">
                                        <i class="bx bx-refresh me-2"></i>Reset Password Automatically
                                    </button>
                                    <input type="hidden" name="auto_generate_password" id="auto_generate_password" value="0">
                                    <input type="hidden" name="generated_password_value" id="generated_password_value" value="">
                                </div>
                            </div>
                            @else
                            <div class="alert alert-secondary">
                                <i class="bx bx-lock me-2"></i>
                                <small>Password reset is only available to System Administrators.</small>
                            </div>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Account Status</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <span id="statusText">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                    <small class="text-muted d-block">Toggle to activate or deactivate this user account</small>
                                    </label>
                            </div>
                        </div>

                        <!-- Roles Assignment -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-user-check text-info me-1"></i>User Roles <span class="text-danger">*</span>
                                <span class="badge bg-danger ms-2">Admin Only</span>
                            </label>
                            <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                                <i class="bx bx-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Role Assignment:</strong>
                                    <small class="d-block mt-1">Select at least one role for this user. SMS notifications will be sent to both the admin and the user when roles are changed.</small>
                                </div>
                            </div>
                            @if(auth()->user() && auth()->user()->hasRole('System Admin'))
                            <div class="row g-2">
                                @foreach($roles as $role)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check p-3 border rounded hover-shadow">
                                        <input class="form-check-input role-checkbox" 
                                               type="checkbox" 
                                               name="roles[]" 
                                               value="{{ $role->id }}" 
                                               id="role_{{ $role->id }}"
                                               {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">
                                            {{ $role->display_name ?? $role->name }}
                                        </label>
                                        @if($role->description)
                                            <small class="text-muted d-block">{{ $role->description }}</small>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="alert alert-secondary">
                                <i class="bx bx-lock me-2"></i>
                                <small>Role assignment is only available to System Administrators.</small>
                                <div class="mt-2">
                                    <strong>Current Roles:</strong>
                                    <div class="mt-1">
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary me-1">{{ $role->display_name ?? $role->name }}</span>
                                        @endforeach
                                        @if($user->roles->isEmpty())
                                            <span class="text-muted">No roles assigned</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @error('roles')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bx bx-save me-1"></i> Update User
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .hover-shadow:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .form-check-input:checked ~ .form-check-label {
        color: #940000;
    }
    .form-control-plaintext {
        padding: 0.5rem 0;
        min-height: 38px;
        border-bottom: 1px solid #e9ecef;
    }
    .card-header {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .form-switch-lg .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }
    .form-switch-lg .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    /* Ensure SweetAlert2 modal appears above everything including headers */
    .swal2-container {
        z-index: 99999 !important;
    }
    .swal2-popup {
        z-index: 100000 !important;
    }
    .swal2-backdrop-show {
        z-index: 99998 !important;
    }
    /* Ensure header/navbar stays behind modal */
    .ttr-header,
    .layout-navbar,
    .navbar,
    header {
        z-index: 1000 !important;
    }
    /* When modal is open, ensure header is behind */
    body.swal2-shown .ttr-header,
    body.swal2-shown .layout-navbar,
    body.swal2-shown .navbar,
    body.swal2-shown header {
        z-index: 1000 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle status text
        $('#is_active').on('change', function() {
            const statusText = $(this).is(':checked') ? 'Active' : 'Inactive';
            $('#statusText').text(statusText);
        });

        // Real-time role validation feedback
        let roleErrorShown = false;
        $('.role-checkbox').on('change', function() {
            const rolesSelected = $('input[name="roles[]"]:checked').length;
            const errorDiv = $('#roleValidationError');
            
            if (rolesSelected === 0) {
                if (!errorDiv.length) {
                    $('.role-checkbox').first().closest('.mb-4').append(
                        '<div id="roleValidationError" class="text-danger small mt-2"><i class="bx bx-error-circle me-1"></i>Please select at least one role for this user.</div>'
                    );
                }
                roleErrorShown = true;
            } else {
                $('#roleValidationError').remove();
                roleErrorShown = false;
            }
        });

        // Form validation on submit - allow independent field updates
        $('#editUserForm').on('submit', function(e) {
            @if(auth()->user() && auth()->user()->hasRole('System Admin'))
            // Only check roles if System Admin is editing
            const rolesSelected = $('input[name="roles[]"]:checked').length;
            if (rolesSelected === 0) {
                e.preventDefault();
                $('#roleValidationError').remove();
                $('.role-checkbox').first().closest('.mb-4').append(
                    '<div id="roleValidationError" class="text-danger small mt-2"><i class="bx bx-error-circle me-1"></i>Please select at least one role for this user.</div>'
                );
                $('html, body').animate({
                    scrollTop: $('#roleValidationError').offset().top - 100
                }, 500);
                Swal.fire({
                    icon: 'error',
                    title: 'No Role Selected',
                    text: 'Please select at least one role for this user before submitting.',
                    confirmButtonColor: '#940000'
                });
                return false;
            }
            $('#roleValidationError').remove();
            @endif
            
            @if(auth()->user()->id == $user->id && !auth()->user()->hasRole('System Admin'))
            // Check phone OTP verification if staff is editing their own phone
            const phoneChanged = $('#phone').val() !== '{{ $user->phone }}';
            const phoneOtpVerified = $('#phone_otp_verified').val() === '1';
            
            if (phoneChanged && !phoneOtpVerified) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Phone Verification Required',
                    text: 'You must verify your current phone number with OTP before changing it.',
                    confirmButtonColor: '#940000'
                });
                return false;
            }
            @endif
        });

        // Show success/error messages
        @if(session('success'))
            @if(session('generated_password'))
                // Show password reset success with generated password
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset Successful!',
                    html: `
                        <div class="text-start">
                            <p><strong>New Password Generated:</strong></p>
                            <div class="alert alert-warning mb-3">
                                <h4 class="text-center mb-0" style="font-family: monospace; font-size: 1.5rem; letter-spacing: 2px;">
                                    {{ session('generated_password') }}
                                </h4>
                            </div>
                            <p class="mb-2"><strong>User:</strong> {{ session('password_user_name') }} ({{ session('password_user_email') }})</p>
                            @if(session('sms_status'))
                                @php $smsStatus = session('sms_status'); @endphp
                                <div class="mt-3">
                                    <p class="mb-1"><strong>SMS Status:</strong></p>
                                    <ul class="list-unstyled mb-0">
                                        @if($smsStatus['staff_sms_sent'])
                                            <li class="text-success"><i class="bx bx-check-circle"></i> SMS sent to staff member</li>
                                        @else
                                            <li class="text-danger"><i class="bx bx-x-circle"></i> Staff SMS: {{ $smsStatus['staff_sms_error'] ?? 'Not sent' }}</li>
                                        @endif
                                        @if($smsStatus['admin_sms_sent'])
                                            <li class="text-success"><i class="bx bx-check-circle"></i> SMS sent to admin</li>
                                        @else
                                            <li class="text-danger"><i class="bx bx-x-circle"></i> Admin SMS: {{ $smsStatus['admin_sms_error'] ?? 'Not sent' }}</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                            <p class="text-danger mt-3 mb-0"><small><i class="bx bx-info-circle"></i> Please save this password securely. It will not be shown again.</small></p>
                        </div>
                    `,
                    width: '600px',
                    confirmButtonText: 'Copy Password',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#940000',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    backdrop: true,
                    didOpen: () => {
                        // Force z-index to be highest
                        const swalContainer = document.querySelector('.swal2-container');
                        const swalPopup = document.querySelector('.swal2-popup');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                        if (swalPopup) {
                            swalPopup.style.zIndex = '100000';
                        }
                        
                        // Ensure header is behind
                        const headers = document.querySelectorAll('.ttr-header, .layout-navbar, .navbar, header');
                        headers.forEach(header => {
                            header.style.zIndex = '1000';
                        });
                        
                        // Add copy button functionality
                        const copyBtn = document.querySelector('.swal2-confirm');
                        if (copyBtn) {
                            copyBtn.addEventListener('click', function() {
                                const password = '{{ session('generated_password') }}';
                                navigator.clipboard.writeText(password).then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Copied!',
                                        text: 'Password copied to clipboard',
                                        timer: 2000,
                                        showConfirmButton: false,
                                        didOpen: () => {
                                            const swalContainer2 = document.querySelector('.swal2-container');
                                            const swalPopup2 = document.querySelector('.swal2-popup');
                                            if (swalContainer2) swalContainer2.style.zIndex = '99999';
                                            if (swalPopup2) swalPopup2.style.zIndex = '100000';
                                        }
                                    });
                                });
                            });
                        }
                    }
                });
            @else
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}'
            });
        @endif
    });

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + 'Icon');
        
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

    // Generate password and send SMS immediately when button is clicked
    async function generateAndShowPassword() {
        const btn = document.getElementById('resetPasswordBtn');
        const originalBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending SMS...';
        
        try {
            // Call backend to generate password and send SMS immediately
            const response = await fetch('{{ route("admin.users.send-password-reset-sms", $user) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const newPassword = data.password;
                const smsStatus = data.sms_status;
                
                // Store password in hidden field
                document.getElementById('auto_generate_password').value = '1';
                document.getElementById('generated_password_value').value = newPassword;
                
                // Update button state
                btn.innerHTML = '<i class="bx bx-check me-2"></i>Password Generated & SMS Sent';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
                
                // Build SMS status HTML
                let smsStatusHtml = '<div class="mt-3"><p class="mb-1"><strong>SMS Status:</strong></p><ul class="list-unstyled mb-0">';
                if (smsStatus.staff_sms_sent) {
                    smsStatusHtml += '<li class="text-success"><i class="bx bx-check-circle"></i> SMS sent to staff member</li>';
                } else {
                    smsStatusHtml += '<li class="text-danger"><i class="bx bx-x-circle"></i> Staff SMS: ' + (smsStatus.staff_sms_error || 'Not sent') + '</li>';
                }
                if (smsStatus.admin_sms_sent) {
                    smsStatusHtml += '<li class="text-success"><i class="bx bx-check-circle"></i> SMS sent to admin</li>';
                } else {
                    smsStatusHtml += '<li class="text-danger"><i class="bx bx-x-circle"></i> Admin SMS: ' + (smsStatus.admin_sms_error || 'Not sent') + '</li>';
                }
                smsStatusHtml += '</ul></div>';
                
                // Show password immediately with SMS status
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset Successful!',
                    html: `
                        <div class="text-start">
                            <p><strong>New Password Generated:</strong></p>
                            <div class="alert alert-warning mb-3">
                                <h4 class="text-center mb-0" style="font-family: monospace; font-size: 1.5rem; letter-spacing: 2px;">
                                    ${newPassword}
                                </h4>
                            </div>
                            <p class="mb-2"><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</p>
                            <p class="text-success mb-2"><i class="bx bx-check-circle"></i> SMS notifications have been sent immediately!</p>
                            ${smsStatusHtml}
                            <p class="text-success mt-3 mb-0"><small><i class="bx bx-check-circle"></i> Password has been saved to database immediately!</small></p>
                            <p class="text-danger mt-2 mb-0"><small><i class="bx bx-info-circle"></i> Please save this password securely. It will not be shown again.</small></p>
                        </div>
                    `,
                    width: '600px',
                    confirmButtonText: 'Copy Password',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#940000',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    backdrop: true,
                    didOpen: () => {
                        // Force z-index to be highest
                        const swalContainer = document.querySelector('.swal2-container');
                        const swalPopup = document.querySelector('.swal2-popup');
                        if (swalContainer) swalContainer.style.zIndex = '99999';
                        if (swalPopup) swalPopup.style.zIndex = '100000';
                        
                        // Ensure header is behind
                        const headers = document.querySelectorAll('.ttr-header, .layout-navbar, .navbar, header');
                        headers.forEach(header => header.style.zIndex = '1000');
                        
                        // Add copy button functionality
                        const copyBtn = document.querySelector('.swal2-confirm');
                        if (copyBtn) {
                            copyBtn.addEventListener('click', function() {
                                navigator.clipboard.writeText(newPassword).then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Copied!',
                                        text: 'Password copied to clipboard',
                                        timer: 2000,
                                        showConfirmButton: false,
                                        didOpen: () => {
                                            const swalContainer2 = document.querySelector('.swal2-container');
                                            const swalPopup2 = document.querySelector('.swal2-popup');
                                            if (swalContainer2) swalContainer2.style.zIndex = '99999';
                                            if (swalPopup2) swalPopup2.style.zIndex = '100000';
                                        }
                                    });
                                });
                            });
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to generate password and send SMS. Please try again.'
                });
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while generating password. Please try again.'
            });
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        }
    }
    
    // Phone OTP functionality for staff editing their own phone
    @if(auth()->user()->id == $user->id && !auth()->user()->hasRole('System Admin'))
    let phoneOtpVerified = false;
    
    document.getElementById('sendPhoneOtpBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
        
        try {
            const response = await fetch('{{ route("account.settings.phone.otp.send") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('phoneOtpSection').style.display = 'block';
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent!',
                    text: 'OTP has been sent to your current phone number. Valid for 10 minutes.',
                    timer: 3000,
                    showConfirmButton: false
                });
                btn.style.display = 'none';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data.message || 'Failed to send OTP. Please try again.'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-send me-1"></i>Send OTP';
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-send me-1"></i>Send OTP';
        }
    });
    
    document.getElementById('verifyPhoneOtpBtn').addEventListener('click', async function() {
        const otpCode = document.getElementById('phoneOtpCode').value.trim();
        if (!otpCode || otpCode.length !== 6) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'Please enter a valid 6-digit OTP code.'
            });
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';
        
        try {
            const formData = new FormData();
            formData.append('otp_code', otpCode);
            
            const response = await fetch('{{ route("account.settings.phone.otp.verify") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                phoneOtpVerified = true;
                document.getElementById('phone_otp_verified').value = '1';
                document.getElementById('phoneOtpSection').style.display = 'none';
                document.getElementById('phone').removeAttribute('readonly');
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Verified!',
                    text: 'You can now update your phone number.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: data.message || 'Invalid or expired OTP. Please try again.'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-check me-1"></i>Verify OTP';
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-check me-1"></i>Verify OTP';
        }
    });
    @endif
</script>
@endpush
@endsection

