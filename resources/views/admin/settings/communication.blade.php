@extends('layouts.app')

@section('title', 'Communication Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .comm-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .comm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
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
    .test-button {
        position: relative;
        overflow: hidden;
    }
    .test-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    .test-button:active::before {
        width: 300px;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-warning" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-envelope me-2"></i>Communication Settings
                            </h3>
                            <p class="mb-0 text-white-50">Configure SMS and Email services for system notifications</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" onclick="checkAllConnections()">
                                <i class="bx bx-refresh me-1"></i>Check Connections
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

    <!-- Connection Status -->
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
                        <i class="bx bx-envelope fs-1 text-danger"></i>
                    </div>
                    <h5 class="mb-2">Email Service</h5>
                    <div id="emailStatus" class="status-badge status-checking mb-2">
                        <i class="bx bx-loader bx-spin me-1"></i>Checking...
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="checkEmailStatus()">
                        <i class="bx bx-refresh me-1"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="communicationSettingsForm">
        @csrf
        <div class="row">
            <!-- SMS Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4 comm-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-message-rounded-dots me-2 text-primary"></i>SMS Gateway Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">SMS Username</label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="sms_username" name="sms_username" 
                                       placeholder="Enter SMS gateway username">
                                <small class="text-muted">SMS gateway API username</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">SMS Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" 
                                           id="sms_password" name="sms_password" 
                                           placeholder="Enter SMS gateway password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('sms_password')">
                                        <i class="bx bx-show" id="sms_password_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">SMS gateway API password</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMS From (Sender Name)</label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="sms_from" name="sms_from" 
                                       placeholder="e.g., OfisiLink">
                                <small class="text-muted">Name displayed as sender</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMS API URL</label>
                                <input type="url" class="form-control form-control-lg" 
                                       id="sms_url" name="sms_url" 
                                       placeholder="https://messaging-service.co.tz/api/sms">
                                <small class="text-muted">SMS gateway API endpoint</small>
                            </div>
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <label class="form-label fw-bold mb-2">Test SMS</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">
                                                <i class="bx bx-phone"></i>
                                            </span>
                                            <input type="text" class="form-control" 
                                                   id="testSmsPhone" 
                                                   placeholder="255712345678" 
                                                   pattern="^255[0-9]{9}$">
                                            <button type="button" class="btn btn-primary test-button" onclick="testSMS()">
                                                <i class="bx bx-send me-1"></i>Send Test
                                            </button>
                                        </div>
                                        <small class="text-muted">Format: 255XXXXXXXXX (12 digits)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4 comm-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-envelope me-2 text-danger"></i>Email (SMTP) Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mailer Type</label>
                                <select class="form-select form-select-lg" id="mail_mailer" name="mail_mailer">
                                    <option value="smtp">SMTP</option>
                                    <option value="sendmail">Sendmail</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMTP Host</label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="mail_host" name="mail_host" 
                                       placeholder="smtp.gmail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMTP Port</label>
                                <input type="number" class="form-control form-control-lg" 
                                       id="mail_port" name="mail_port" 
                                       placeholder="587" min="1" max="65535">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Encryption</label>
                                <select class="form-select form-select-lg" id="mail_encryption" name="mail_encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">SMTP Username</label>
                                <input type="email" class="form-control form-control-lg" 
                                       id="mail_username" name="mail_username" 
                                       placeholder="your-email@gmail.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">SMTP Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" 
                                           id="mail_password" name="mail_password" 
                                           placeholder="Enter SMTP password or app password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('mail_password')">
                                        <i class="bx bx-show" id="mail_password_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">For Gmail, use App Password</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">From Email Address</label>
                                <input type="email" class="form-control form-control-lg" 
                                       id="mail_from_address" name="mail_from_address" 
                                       placeholder="noreply@ofisilink.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">From Name</label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="mail_from_name" name="mail_from_name" 
                                       placeholder="OfisiLink System">
                            </div>
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <label class="form-label fw-bold mb-2">Test Email</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">
                                                <i class="bx bx-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" 
                                                   id="testEmailAddress" 
                                                   placeholder="test@example.com">
                                            <button type="button" class="btn btn-danger test-button" onclick="testEmail()">
                                                <i class="bx bx-send me-1"></i>Send Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Ready to save your communication settings?</h6>
                                <small class="text-muted">All SMS and Email configurations will be updated</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.settings.organization') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="button" class="btn btn-warning btn-lg" onclick="saveCommunicationSettings()">
                                    <i class="bx bx-save me-1"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Configured Email Providers -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-envelope me-2 text-danger"></i>Configured Email Accounts
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmailProviderModal">
                        <i class="bx bx-plus me-1"></i>Add Email Provider
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Host</th>
                                    <th>Port</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th>Last Test</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emailProviders ?? [] as $provider)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($provider->is_primary)
                                                <span class="badge bg-primary me-2">Primary</span>
                                            @endif
                                            <strong>{{ $provider->name }}</strong>
                                        </div>
                                        @if($provider->description)
                                            <small class="text-muted d-block">{{ Str::limit($provider->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $provider->mail_host ?? 'N/A' }}</td>
                                    <td>{{ $provider->mail_port ?? 'N/A' }}</td>
                                    <td>{{ $provider->mail_username ? Str::limit($provider->mail_username, 30) : 'N/A' }}</td>
                                    <td>
                                        @if($provider->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($provider->last_tested_at)
                                            <div>{{ $provider->last_tested_at->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $provider->last_tested_at->format('g:i A') }}</small>
                                            @if($provider->last_test_status)
                                                <span class="badge bg-success ms-1">Success</span>
                                            @else
                                                <span class="badge bg-danger ms-1">Failed</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never tested</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="testProviderConnection({{ $provider->id }}, 'email')"
                                                    title="Test Connection">
                                                <i class="bx bx-refresh"></i>
                                            </button>
                                            <button class="btn btn-outline-info" 
                                                    onclick="editEmailProvider({{ $provider->id }})"
                                                    title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if(!$provider->is_primary)
                                                <button class="btn btn-outline-success" 
                                                        onclick="setPrimaryProvider({{ $provider->id }})"
                                                        title="Set as Primary">
                                                    <i class="bx bx-star"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteProvider({{ $provider->id }})"
                                                        title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-inbox fs-1"></i>
                                            <p class="mt-2 mb-0">No email providers configured.</p>
                                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addEmailProviderModal">
                                                <i class="bx bx-plus me-1"></i>Add Email Provider
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configured SMS Providers -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-message-rounded-dots me-2 text-primary"></i>Configured SMS Gateways
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSmsProviderModal">
                        <i class="bx bx-plus me-1"></i>Add SMS Provider
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Username</th>
                                    <th>From</th>
                                    <th>Status</th>
                                    <th>Last Test</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($smsProviders ?? [] as $provider)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($provider->is_primary)
                                                <span class="badge bg-primary me-2">Primary</span>
                                            @endif
                                            <strong>{{ $provider->name }}</strong>
                                        </div>
                                        @if($provider->description)
                                            <small class="text-muted d-block">{{ Str::limit($provider->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($provider->sms_url ?? 'N/A', 40) }}</td>
                                    <td>{{ $provider->sms_username ? Str::limit($provider->sms_username, 20) : 'N/A' }}</td>
                                    <td>{{ $provider->sms_from ?? 'N/A' }}</td>
                                    <td>
                                        @if($provider->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($provider->last_tested_at)
                                            <div>{{ $provider->last_tested_at->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $provider->last_tested_at->format('g:i A') }}</small>
                                            @if($provider->last_test_status)
                                                <span class="badge bg-success ms-1">Success</span>
                                            @else
                                                <span class="badge bg-danger ms-1">Failed</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never tested</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="testProviderConnection({{ $provider->id }}, 'sms')"
                                                    title="Test Connection">
                                                <i class="bx bx-refresh"></i>
                                            </button>
                                            <button class="btn btn-outline-info" 
                                                    onclick="editSmsProvider({{ $provider->id }})"
                                                    title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if(!$provider->is_primary)
                                                <button class="btn btn-outline-success" 
                                                        onclick="setPrimaryProvider({{ $provider->id }})"
                                                        title="Set as Primary">
                                                    <i class="bx bx-star"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteProvider({{ $provider->id }})"
                                                        title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-message-rounded-dots fs-1"></i>
                                            <p class="mt-2 mb-0">No SMS providers configured.</p>
                                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addSmsProviderModal">
                                                <i class="bx bx-plus me-1"></i>Add SMS Provider
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Email Provider Modal -->
<div class="modal fade" id="addEmailProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bx bx-envelope me-2"></i>Add Email Provider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEmailProviderForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Primary SMTP Server">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mailer Type <span class="text-danger">*</span></label>
                            <select name="mailer_type" class="form-select" required>
                                <option value="smtp">SMTP</option>
                                <option value="sendmail">Sendmail</option>
                                <option value="mailgun">Mailgun</option>
                                <option value="ses">Amazon SES</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" name="mail_host" class="form-control" required placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMTP Port <span class="text-danger">*</span></label>
                            <input type="number" name="mail_port" class="form-control" required placeholder="587" min="1" max="65535">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Encryption <span class="text-danger">*</span></label>
                            <select name="mail_encryption" class="form-select" required>
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">SMTP Username</label>
                            <input type="email" name="mail_username" class="form-control" placeholder="your-email@gmail.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">SMTP Password</label>
                            <input type="password" name="mail_password" class="form-control" placeholder="Enter password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">From Email Address</label>
                            <input type="email" name="mail_from_address" class="form-control" placeholder="noreply@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">From Name</label>
                            <input type="text" name="mail_from_name" class="form-control" placeholder="OfisiLink System">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="emailIsActive" checked>
                                <label class="form-check-label" for="emailIsActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_primary" id="emailIsPrimary">
                                <label class="form-check-label" for="emailIsPrimary">Set as Primary</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" onclick="testEmailProviderBeforeSave()">
                        <i class="bx bx-refresh me-1"></i>Test Connection
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-save me-1"></i>Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add SMS Provider Modal -->
<div class="modal fade" id="addSmsProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-message-rounded-dots me-2"></i>Add SMS Provider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSmsProviderForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Primary SMS Gateway">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">SMS Username <span class="text-danger">*</span></label>
                            <input type="text" name="sms_username" class="form-control" required placeholder="SMS gateway username">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">SMS Password <span class="text-danger">*</span></label>
                            <input type="password" name="sms_password" class="form-control" required placeholder="SMS gateway password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMS From (Sender Name)</label>
                            <input type="text" name="sms_from" class="form-control" placeholder="OfisiLink">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMS API URL <span class="text-danger">*</span></label>
                            <input type="url" name="sms_url" class="form-control" required placeholder="https://messaging-service.co.tz/api/sms">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="smsIsActive" checked>
                                <label class="form-check-label" for="smsIsActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_primary" id="smsIsPrimary">
                                <label class="form-check-label" for="smsIsPrimary">Set as Primary</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" onclick="testSmsProviderBeforeSave()">
                        <i class="bx bx-refresh me-1"></i>Test Connection
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const token = '{{ csrf_token() }}';

// Load communication settings on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCommunicationSettings();
    checkAllConnections();
});

function loadCommunicationSettings() {
    fetch('{{ route("admin.settings.communication.data") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
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
        mail_port: parseInt(document.getElementById('mail_port').value) || 587,
        mail_username: document.getElementById('mail_username').value,
        mail_password: document.getElementById('mail_password').value,
        mail_encryption: document.getElementById('mail_encryption').value,
        mail_from_address: document.getElementById('mail_from_address').value,
        mail_from_name: document.getElementById('mail_from_name').value,
    };

    Swal.fire({
        title: 'Saving Settings...',
        html: 'Please wait while we save your communication settings',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('{{ route("admin.settings.communication.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Communication settings saved successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                checkAllConnections();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to save communication settings',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
}

function testSMS() {
    const phone = document.getElementById('testSmsPhone').value.trim();
    if(!phone) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a phone number',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Validate phone format
    if(!/^255[0-9]{9}$/.test(phone)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Format',
            text: 'Phone number must be in format: 255XXXXXXXXX (12 digits)',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Sending Test SMS...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Sending test SMS to ${phone}...</p>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    fetch('{{ route("admin.settings.communication.test-sms") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: `
                    <p>${data.message || 'Test SMS sent successfully'}</p>
                    <p class="text-muted small mt-2">Check your phone for the test message.</p>
                `,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                html: `
                    <p>${data.message || 'Failed to send test SMS'}</p>
                    <p class="text-muted small mt-2">Please check your SMS configuration and try again.</p>
                `,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
}

function testEmail() {
    const email = document.getElementById('testEmailAddress').value.trim();
    if(!email) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter an email address',
            confirmButtonText: 'OK'
        });
        return;
    }

    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Sending Test Email...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-danger mb-3" role="status"></div>
                <p>Sending test email to ${email}...</p>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    fetch('{{ route("admin.settings.communication.test-email") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: `
                    <p>${data.message || 'Test email sent successfully'}</p>
                    <p class="text-muted small mt-2">Check your inbox (and spam folder) for the test message.</p>
                `,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                html: `
                    <p>${data.message || 'Failed to send test email'}</p>
                    <p class="text-muted small mt-2">Please check your email configuration and try again.</p>
                `,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
}

function checkSMSStatus() {
    const statusEl = document.getElementById('smsStatus');
    statusEl.className = 'status-badge status-checking mb-2';
    statusEl.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Checking...';

    fetch('{{ route("admin.settings.communication.check-sms") }}')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.status === 'connected') {
                statusEl.className = 'status-badge status-connected mb-2';
                statusEl.innerHTML = '<i class="bx bx-check-circle me-1"></i>Connected';
            } else {
                statusEl.className = 'status-badge status-disconnected mb-2';
                statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Disconnected';
            }
        })
        .catch(err => {
            statusEl.className = 'status-badge status-disconnected mb-2';
            statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Error';
        });
}

function checkEmailStatus() {
    const statusEl = document.getElementById('emailStatus');
    statusEl.className = 'status-badge status-checking mb-2';
    statusEl.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Checking...';

    fetch('{{ route("admin.settings.communication.check-email") }}')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.status === 'connected') {
                statusEl.className = 'status-badge status-connected mb-2';
                statusEl.innerHTML = '<i class="bx bx-check-circle me-1"></i>Connected';
            } else {
                statusEl.className = 'status-badge status-disconnected mb-2';
                statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Disconnected';
            }
        })
        .catch(err => {
            statusEl.className = 'status-badge status-disconnected mb-2';
            statusEl.innerHTML = '<i class="bx bx-x-circle me-1"></i>Error';
        });
}

function checkAllConnections() {
    checkSMSStatus();
    checkEmailStatus();
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if(field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        field.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
}

// Test provider connection with progress bar
async function testProviderConnection(providerId, type) {
    let progress = 0;
    
    Swal.fire({
        title: 'Testing Connection...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Testing ${type === 'email' ? 'Email' : 'SMS'} connection...</p>
                <div class="progress mt-3" style="height: 30px; border-radius: 15px;">
                    <div id="testProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%; font-size: 14px; line-height: 30px; font-weight: bold;">
                        0%
                    </div>
                </div>
                <p class="text-muted small mt-2" id="testStatus">Connecting to server...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            // Simulate progress
            const progressInterval = setInterval(() => {
                progress += 10;
                if (progress <= 90) {
                    const progressBar = document.getElementById('testProgressBar');
                    const statusText = document.getElementById('testStatus');
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                        progressBar.textContent = progress + '%';
                    }
                    if (statusText) {
                        if (progress < 30) {
                            statusText.textContent = 'Connecting to server...';
                        } else if (progress < 60) {
                            statusText.textContent = 'Authenticating...';
                        } else {
                            statusText.textContent = 'Verifying connection...';
                        }
                    }
                }
            }, 300);
            
            // Make actual test request
            fetch(`/admin/settings/notification-providers/${providerId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    test_email: type === 'email' ? prompt('Enter test email address:') || 'test@example.com' : undefined,
                    test_phone: type === 'sms' ? prompt('Enter test phone number (255XXXXXXXXX):') || '255712345678' : undefined
                })
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(progressInterval);
                const progressBar = document.getElementById('testProgressBar');
                if (progressBar) {
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    progressBar.classList.remove('progress-bar-animated');
                }
                
                setTimeout(() => {
                    Swal.close();
                    if(data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Connection Successful!',
                            html: `
                                <p>${data.message || 'Connection test passed successfully'}</p>
                                <p class="text-success mt-2">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <strong>Status: CONNECTED</strong>
                                </p>
                            `,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed',
                            html: `
                                <p>${data.message || 'Connection test failed'}</p>
                                <p class="text-danger mt-2">
                                    <i class="bx bx-x-circle me-1"></i>
                                    <strong>Status: DISCONNECTED</strong>
                                </p>
                                <p class="text-muted small mt-2">Please check your configuration settings and try again.</p>
                            `,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545',
                            width: '600px'
                        }).then(() => {
                            location.reload();
                        });
                    }
                }, 500);
            })
            .catch(err => {
                clearInterval(progressInterval);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    html: `
                        <p>Failed to test connection: ${err.message || 'Network error occurred'}</p>
                        <p class="text-muted small mt-2">Please check your internet connection and try again.</p>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Add Email Provider
$('#addEmailProviderForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $(this).find('input[name="name"]').val(),
        type: 'email',
        mailer_type: $(this).find('select[name="mailer_type"]').val(),
        mail_host: $(this).find('input[name="mail_host"]').val(),
        mail_port: parseInt($(this).find('input[name="mail_port"]').val()),
        mail_encryption: $(this).find('select[name="mail_encryption"]').val(),
        mail_username: $(this).find('input[name="mail_username"]').val(),
        mail_password: $(this).find('input[name="mail_password"]').val(),
        mail_from_address: $(this).find('input[name="mail_from_address"]').val(),
        mail_from_name: $(this).find('input[name="mail_from_name"]').val(),
        description: $(this).find('textarea[name="description"]').val(),
        is_active: $(this).find('input[name="is_active"]').is(':checked'),
        is_primary: $(this).find('input[name="is_primary"]').is(':checked'),
        priority: 100
    };
    
    Swal.fire({
        title: 'Saving...',
        html: 'Please wait while we save the email provider',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('{{ route("admin.settings.notification-providers.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Email provider added successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#addEmailProviderModal').modal('hide');
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to add email provider',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
});

// Add SMS Provider
$('#addSmsProviderForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: $(this).find('input[name="name"]').val(),
        type: 'sms',
        sms_username: $(this).find('input[name="sms_username"]').val(),
        sms_password: $(this).find('input[name="sms_password"]').val(),
        sms_from: $(this).find('input[name="sms_from"]').val(),
        sms_url: $(this).find('input[name="sms_url"]').val(),
        description: $(this).find('textarea[name="description"]').val(),
        is_active: $(this).find('input[name="is_active"]').is(':checked'),
        is_primary: $(this).find('input[name="is_primary"]').is(':checked'),
        priority: 100
    };
    
    Swal.fire({
        title: 'Saving...',
        html: 'Please wait while we save the SMS provider',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('{{ route("admin.settings.notification-providers.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'SMS provider added successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#addSmsProviderModal').modal('hide');
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to add SMS provider',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
});

// Test Email Provider Before Save
function testEmailProviderBeforeSave() {
    const form = document.getElementById('addEmailProviderForm');
    const host = form.querySelector('input[name="mail_host"]').value;
    const port = form.querySelector('input[name="mail_port"]').value;
    const username = form.querySelector('input[name="mail_username"]').value;
    const password = form.querySelector('input[name="mail_password"]').value;
    const encryption = form.querySelector('select[name="mail_encryption"]').value;
    
    if(!host || !port) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Configuration',
            text: 'Please enter SMTP host and port before testing',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    let progress = 0;
    
    Swal.fire({
        title: 'Testing Connection...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Testing SMTP connection to ${host}:${port}...</p>
                <div class="progress mt-3" style="height: 30px; border-radius: 15px;">
                    <div id="testProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%; font-size: 14px; line-height: 30px; font-weight: bold;">
                        0%
                    </div>
                </div>
                <p class="text-muted small mt-2" id="testStatus">Connecting to server...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            const progressInterval = setInterval(() => {
                progress += 10;
                if (progress <= 90) {
                    const progressBar = document.getElementById('testProgressBar');
                    const statusText = document.getElementById('testStatus');
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                        progressBar.textContent = progress + '%';
                    }
                    if (statusText) {
                        if (progress < 30) {
                            statusText.textContent = 'Connecting to server...';
                        } else if (progress < 60) {
                            statusText.textContent = 'Authenticating...';
                        } else {
                            statusText.textContent = 'Verifying connection...';
                        }
                    }
                }
            }, 300);
            
            // Test connection using EmailService
            fetch('/admin/settings/communication/test-email', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: username || prompt('Enter test email address:') || 'test@example.com',
                    test_config: {
                        host: host,
                        port: parseInt(port),
                        username: username,
                        password: password,
                        encryption: encryption
                    }
                })
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(progressInterval);
                const progressBar = document.getElementById('testProgressBar');
                if (progressBar) {
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    progressBar.classList.remove('progress-bar-animated');
                }
                
                setTimeout(() => {
                    Swal.close();
                    if(data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Connection Successful!',
                            html: `
                                <p>${data.message || 'SMTP connection test passed'}</p>
                                <p class="text-success mt-2">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <strong>Status: CONNECTED</strong>
                                </p>
                            `,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        });
                    } else {
                        let errorMsg = data.message || data.error || 'Connection test failed';
                        let suggestion = data.suggestion || '';
                        
                        // Improve error message for timeout errors
                        if (errorMsg.includes('10060') || errorMsg.includes('timeout') || 
                            errorMsg.includes('did not properly respond') || 
                            errorMsg.includes('Connection failed')) {
                            errorMsg = 'Connection timeout: The SMTP server did not respond. Please check:<br>' +
                                      '1. SMTP host and port are correct<br>' +
                                      '2. Firewall is not blocking the connection<br>' +
                                      '3. Server is accessible from this network<br>' +
                                      '4. SSL/TLS settings match the server requirements<br>' +
                                      '5. Try alternative port (587 for TLS, 465 for SSL)';
                        }
                        
                        let htmlContent = `
                            <p>${errorMsg}</p>
                            <p class="text-danger mt-2">
                                <i class="bx bx-x-circle me-1"></i>
                                <strong>Status: DISCONNECTED</strong>
                            </p>
                        `;
                        
                        if (suggestion) {
                            htmlContent += `<p class="text-info small mt-2"><strong>Tip:</strong> ${suggestion}</p>`;
                        } else {
                            htmlContent += `<p class="text-muted small mt-2">Please verify your SMTP settings and try again.</p>`;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed',
                            html: htmlContent,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545',
                            width: '600px'
                        });
                    }
                }, 500);
            })
            .catch(err => {
                clearInterval(progressInterval);
                Swal.close();
                let errorMsg = err.message || 'Network error occurred';
                if (errorMsg.includes('10060') || errorMsg.includes('timeout')) {
                    errorMsg = 'Connection timeout: The SMTP server did not respond. Please check your host and port settings.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    html: `
                        <p>${errorMsg}</p>
                        <p class="text-muted small mt-2">Please check your internet connection and SMTP server settings.</p>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                    width: '600px'
                });
            });
        }
    });
}

// Test SMS Provider Before Save
function testSmsProviderBeforeSave() {
    const form = document.getElementById('addSmsProviderForm');
    const url = form.querySelector('input[name="sms_url"]').value;
    const username = form.querySelector('input[name="sms_username"]').value;
    const password = form.querySelector('input[name="sms_password"]').value;
    
    if(!url || !username || !password) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Configuration',
            text: 'Please enter SMS URL, username, and password before testing',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Testing SMS Connection...',
        html: 'Please wait while we test the SMS gateway connection',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/admin/settings/communication/test-sms', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone: prompt('Enter test phone number (255XXXXXXXXX):') || '255712345678'
        })
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Connection Successful!',
                text: data.message || 'SMS gateway connection test passed',
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: data.message || 'SMS gateway connection test failed',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    });
}

// Set Primary Provider
function setPrimaryProvider(providerId) {
    Swal.fire({
        title: 'Set as Primary?',
        text: 'This will set this provider as the primary provider for its type.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, set as primary',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/settings/notification-providers/${providerId}/set-primary`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Provider set as primary successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to set provider as primary',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Delete Provider
function deleteProvider(providerId) {
    Swal.fire({
        title: 'Delete Provider?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/settings/notification-providers/${providerId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Provider deleted successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to delete provider',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Edit Email Provider
function editEmailProvider(providerId) {
    // Load provider data and open edit modal
    fetch(`/admin/settings/notification-providers/${providerId}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success && data.provider) {
            const provider = data.provider;
            // Populate edit form (you'll need to create an edit modal similar to add modal)
            Swal.fire({
                icon: 'info',
                title: 'Edit Provider',
                text: 'Edit functionality will be implemented. For now, please delete and recreate the provider.',
                confirmButtonText: 'OK'
            });
        }
    });
}

// Edit SMS Provider
function editSmsProvider(providerId) {
    // Similar to editEmailProvider
    Swal.fire({
        icon: 'info',
        title: 'Edit Provider',
        text: 'Edit functionality will be implemented. For now, please delete and recreate the provider.',
        confirmButtonText: 'OK'
    });
}
</script>
@endpush

