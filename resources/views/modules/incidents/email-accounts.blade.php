@extends('layouts.app')

@section('title', 'Configured Email Accounts - Incident Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .email-config-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .email-config-card:hover {
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
    .status-failed {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
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
    
    /* Ensure modals appear in front of everything */
    .modal {
        z-index: 100000 !important;
    }
    .modal-backdrop {
        z-index: 99999 !important;
    }
    .modal-dialog {
        z-index: 100001 !important;
        position: relative;
    }
    .modal-content {
        z-index: 100002 !important;
        position: relative;
    }
    
    /* Ensure SweetAlert2 appears in front */
    .swal2-container {
        z-index: 1000000 !important;
    }
    .swal2-popup {
        z-index: 1000001 !important;
    }
    .swal2-backdrop-show {
        z-index: 999999 !important;
    }
    
    /* Ensure all popups are on top */
    [class*="popup"],
    [class*="modal"],
    [class*="swal"],
    [class*="toast"] {
        z-index: 1000000 !important;
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
                                <i class="bx bx-envelope me-2"></i>Configured Email Accounts
                            </h3>
                            <p class="mb-0 text-white-50">Manage and configure email accounts for incident synchronization</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEmailModal" onclick="clearEmailForm()">
                                <i class="bx bx-plus me-1"></i>Add Email Account
                            </button>
                            <a href="{{ route('modules.incidents.email.config') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Email Config
                            </a>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light">
                                <i class="bx bx-home me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Configurations Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm email-config-card">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-envelope me-2 text-warning"></i>Email Accounts
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addEmailModal" onclick="clearEmailForm()">
                            <i class="bx bx-plus me-1"></i>Add Email Account
                        </button>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEmailModal" onclick="quickSetupGmail()">
                            <i class="bx bx-zap me-1"></i>Quick Setup: david.ngungila@emca.tech
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Email Address</th>
                                    <th>Protocol</th>
                                    <th>Host:Port</th>
                                    <th>Connection Status</th>
                                    <th>Last Test</th>
                                    <th>Last Sync</th>
                                    <th>Sync Stats</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($configs as $config)
                                <tr id="config-row-{{ $config->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $config->email_address }}</strong>
                                                @if($config->folder)
                                                <br><small class="text-muted">Folder: {{ $config->folder }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ strtoupper($config->protocol) }}</span>
                                        @if($config->ssl_enabled)
                                        <span class="badge bg-success ms-1">SSL</span>
                                        @else
                                        <span class="badge bg-warning ms-1">TLS</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $config->host }}:{{ $config->port }}</code>
                                    </td>
                                    <td>
                                        @php
                                        $status = $config->connection_status ?? 'unknown';
                                        $syncSettings = $config->sync_settings ?? [];
                                        $isLiveMode = isset($syncSettings['live_mode']) && $syncSettings['live_mode'] === true;
                                        @endphp
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            @if($status === 'connected')
                                            <span class="status-badge status-connected">
                                                <i class="bx bx-check-circle me-1"></i>Connected
                                            </span>
                                            @elseif($status === 'disconnected')
                                            <span class="status-badge status-disconnected">
                                                <i class="bx bx-wifi me-1"></i>Disconnected
                                            </span>
                                            @elseif($status === 'failed')
                                            <span class="status-badge status-failed">
                                                <i class="bx bx-error-circle me-1"></i>Failed
                                            </span>
                                            @else
                                            <span class="status-badge status-checking">
                                                <i class="bx bx-question-mark me-1"></i>Unknown
                                            </span>
                                            @endif
                                            @if($isLiveMode && $config->is_active)
                                            <span class="badge bg-info" title="Auto-syncing every 1 minute">
                                                <i class="bx bx-sync bx-spin me-1"></i>Live Sync
                                            </span>
                                            @endif
                                        </div>
                                        @if($config->connection_error)
                                        <small class="text-danger d-block mt-1" style="font-size: 0.75rem;">
                                            <i class="bx bx-info-circle me-1"></i>
                                            {{ strlen($config->connection_error) > 50 ? substr($config->connection_error, 0, 50) . '...' : $config->connection_error }}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($config->last_connection_test_at)
                                        <div>{{ $config->last_connection_test_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $config->last_connection_test_at->format('g:i A') }}</small>
                                        @else
                                        <span class="text-muted">Never tested</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($config->last_sync_at)
                                        <div>{{ $config->last_sync_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $config->last_sync_at->format('g:i A') }}</small>
                                        @else
                                        <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-success">
                                                <i class="bx bx-check me-1"></i>{{ $config->sync_count ?? 0 }} successful
                                            </small>
                                            @if($config->failed_sync_count > 0)
                                            <small class="text-danger">
                                                <i class="bx bx-x me-1"></i>{{ $config->failed_sync_count }} failed
                                            </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm {{ $config->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                onclick="toggleStatus({{ $config->id }})"
                                                title="Click to {{ $config->is_active ? 'deactivate' : 'activate' }}">
                                            <i class="bx bx-{{ $config->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                            {{ $config->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="testConnection({{ $config->id }})"
                                                    title="Test Connection">
                                                <i class="bx bx-refresh"></i>
                                            </button>
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewConfigDetails({{ $config->id }})"
                                                    title="View Details"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewConfigModal">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="editConfig({{ $config->id }})"
                                                    title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteConfig({{ $config->id }})"
                                                    title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-inbox fs-1"></i>
                                            <p class="mt-2 mb-0">No email configurations found.</p>
                                            <button class="btn btn-warning mt-3" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                                                <i class="bx bx-plus me-1"></i>Add Your First Email Account
                                            </button>
                                            <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#addEmailModal" onclick="quickSetupGmail()">
                                                <i class="bx bx-zap me-1"></i>Quick Setup: david.ngungila@emca.tech
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

<!-- Add Email Modal -->
<div class="modal fade" id="addEmailModal" tabindex="-1" style="z-index: 100000;">
    <div class="modal-dialog modal-lg" style="z-index: 100001;">
        <div class="modal-content" style="border-radius: 12px; z-index: 100002;">
            <div class="modal-header bg-warning text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-envelope me-2"></i>Add Email Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="emailConfigForm" method="POST" action="{{ route('modules.incidents.email.config.store') }}" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" name="email_address" placeholder="your-email@gmail.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Protocol Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" name="protocol" id="protocolSelect" required onchange="updatePort()">
                                <option value="imap" selected>IMAP</option>
                                <option value="pop3">POP3</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="host" placeholder="imap.gmail.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" name="port" id="portInput" placeholder="993" min="1" max="65535" value="993" required>
                            <small class="text-muted">IMAP: 993 (SSL) or 143 (TLS), POP3: 995 (SSL) or 110 (TLS)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Encryption <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="encryptionSelect" name="encryption" onchange="updatePort()">
                                <option value="ssl" selected>SSL</option>
                                <option value="tls">TLS</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="username" placeholder="username or email" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" name="password" id="passwordInput" placeholder="Enter email password or app password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passwordInput')">
                                    <i class="bx bx-show" id="passwordInput_icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">For Gmail, use App Password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Folder (IMAP only)</label>
                            <input type="text" class="form-control form-control-lg" name="folder" value="INBOX" placeholder="INBOX">
                            <small class="text-muted">Leave blank for POP3</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" checked>
                                <label class="form-check-label" for="isActiveCheck">Active (Enable automatic email syncing)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i>Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Email Config Details Modal -->
<div class="modal fade" id="viewConfigModal" tabindex="-1" style="z-index: 100000;">
    <div class="modal-dialog modal-lg" style="z-index: 100001;">
        <div class="modal-content" style="border-radius: 12px; z-index: 100002;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-envelope me-2"></i>Email Account Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewConfigModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="viewConfigEditBtn" onclick="editConfigFromView()">
                    <i class="bx bx-edit me-1"></i>Edit Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Email Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" style="z-index: 100000;">
    <div class="modal-dialog modal-lg" style="z-index: 100001;">
        <div class="modal-content" style="border-radius: 12px; z-index: 100002;">
            <div class="modal-header bg-warning text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-envelope me-2"></i>Edit Email Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEmailConfigForm">
                <input type="hidden" id="editConfigId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" id="editEmailAddress" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Protocol Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="editProtocol" required onchange="updateEditPort()">
                                <option value="imap">IMAP</option>
                                <option value="pop3">POP3</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="editHost" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" id="editPort" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Encryption <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="editEncryptionSelect" onchange="updateEditPort()">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="editUsername" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="editPassword" placeholder="Leave blank to keep current password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('editPassword')">
                                    <i class="bx bx-show" id="editPassword_icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Folder (IMAP only)</label>
                            <input type="text" class="form-control form-control-lg" id="editFolder" value="INBOX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">Active (Enable automatic email syncing)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i>Update Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
let configData = @json($configs);

// Ensure all modals and popups appear in front
(function() {
    // Fix modal z-index on show
    document.addEventListener('show.bs.modal', function(e) {
        const modal = e.target;
        modal.style.zIndex = '100000';
        // Remove duplicate backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach((bd, idx) => {
            if (idx < backdrops.length - 1) bd.remove();
        });
    });
    
    document.addEventListener('shown.bs.modal', function(e) {
        const modal = e.target;
        modal.style.zIndex = '100000';
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.zIndex = '100001';
        }
        // Ensure backdrop is behind modal
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.zIndex = '99999';
            }
            // Remove duplicate backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                for (let i = 0; i < backdrops.length - 1; i++) {
                    backdrops[i].remove();
                }
            }
        }, 50);
    });
    
    // Fix SweetAlert2 z-index
    if (typeof Swal !== 'undefined') {
        const originalFire = Swal.fire;
        Swal.fire = function(...args) {
            const result = originalFire.apply(this, args);
            setTimeout(() => {
                const swalContainer = document.querySelector('.swal2-container');
                const swalPopup = document.querySelector('.swal2-popup');
                if (swalContainer) {
                    swalContainer.style.zIndex = '1000000';
                }
                if (swalPopup) {
                    swalPopup.style.zIndex = '1000001';
                }
                // Ensure backdrop is behind
                const swalBackdrop = document.querySelector('.swal2-backdrop-show');
                if (swalBackdrop) {
                    swalBackdrop.style.zIndex = '999999';
                }
            }, 10);
            return result;
        };
    }
    
    // Periodic check to ensure z-index is maintained
    setInterval(() => {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            if (parseInt(modal.style.zIndex) < 100000) {
                modal.style.zIndex = '100000';
            }
        });
        const swalContainer = document.querySelector('.swal2-container');
        if (swalContainer && parseInt(swalContainer.style.zIndex) < 1000000) {
            swalContainer.style.zIndex = '1000000';
        }
    }, 500);
})();

// Clear email form
function clearEmailForm() {
    const form = document.getElementById('emailConfigForm');
    if (!form) return;
    form.reset();
    const protocolSelect = document.getElementById('protocolSelect');
    if (protocolSelect) protocolSelect.value = 'imap';
    const encryptionSelect = document.getElementById('encryptionSelect');
    if (encryptionSelect) encryptionSelect.value = 'ssl';
    const portInput = document.getElementById('portInput');
    if (portInput) portInput.value = '993';
    const folderInput = form.querySelector('input[name="folder"]');
    if (folderInput) folderInput.value = 'INBOX';
    const activeCheck = document.getElementById('isActiveCheck');
    if (activeCheck) activeCheck.checked = true;
    updatePort();
}

// Quick setup for david.ngungila@emca.tech
function quickSetupGmail() {
    clearEmailForm();
    const form = document.getElementById('emailConfigForm');
    if (!form) return;
    document.getElementById('protocolSelect').value = 'imap';
    form.querySelector('input[name="host"]').value = 'imap.gmail.com';
    document.getElementById('portInput').value = '993';
    document.getElementById('encryptionSelect').value = 'ssl';
    form.querySelector('input[name="email_address"]').value = 'david.ngungila@emca.tech';
    form.querySelector('input[name="username"]').value = 'david.ngungila@emca.tech';
    document.getElementById('passwordInput').value = 'zoym lrqy ggnh giad';
    form.querySelector('input[name="folder"]').value = 'INBOX';
    document.getElementById('isActiveCheck').checked = true;
    updatePort();
}

function updatePort() {
    const protocol = document.getElementById('protocolSelect').value;
    const encryption = document.getElementById('encryptionSelect') ? document.getElementById('encryptionSelect').value : 'ssl';
    const portInput = document.getElementById('portInput');
    if (protocol === 'imap') {
        portInput.value = encryption === 'ssl' ? '993' : '143';
    } else {
        portInput.value = encryption === 'ssl' ? '995' : '110';
    }
}

function updateEditPort() {
    const protocol = document.getElementById('editProtocol').value;
    const encryption = document.getElementById('editEncryptionSelect') ? document.getElementById('editEncryptionSelect').value : 'ssl';
    const portInput = document.getElementById('editPort');
    if (protocol === 'imap') {
        portInput.value = encryption === 'ssl' ? '993' : '143';
    } else {
        portInput.value = encryption === 'ssl' ? '995' : '110';
    }
}

window.togglePassword = function(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const icon = document.getElementById(fieldId + '_icon');
    if (field.type === 'password') {
        field.type = 'text';
        if (icon) {
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
        }
    } else {
        field.type = 'password';
        if (icon) {
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
        }
    }
}

// Test connection
window.testConnection = async function(id) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing...';
    try {
        const res = await fetch(`/modules/incidents/email-config/${id}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Success', data.message, 'success');
            } else {
                alert(data.message);
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message || 'Connection test failed', 'error');
            } else {
                alert(data.message || 'Connection test failed');
            }
        }
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Network error', 'error');
        } else {
            alert('Network error');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// Toggle status
async function toggleStatus(id) {
    try {
        const res = await fetch(`/modules/incidents/email-config/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message || 'Error updating status', 'error');
            } else {
                alert(data.message || 'Error updating status');
            }
        }
    } catch (error) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Network error', 'error');
        } else {
            alert('Network error');
        }
    }
}

// View config details
window.viewConfigDetails = function(id) {
    const config = configData.find(c => c.id === id);
    if (!config) return;
    const modalBody = document.getElementById('viewConfigModalBody');
    const editBtn = document.getElementById('viewConfigEditBtn');
    editBtn.setAttribute('data-config-id', id);
    const syncSettings = config.sync_settings || {};
    const isLiveMode = syncSettings.live_mode === true;
    const lastTest = config.last_connection_test_at ? new Date(config.last_connection_test_at).toLocaleString() : 'Never';
    const lastSync = config.last_sync_at ? new Date(config.last_sync_at).toLocaleString() : 'Never';
    const statusColors = {
        'connected': 'success',
        'disconnected': 'warning',
        'failed': 'danger',
        'unknown': 'secondary'
    };
    const statusColor = statusColors[config.connection_status] || 'secondary';
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3"><i class="bx bx-envelope me-2"></i>Account Information</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="fw-semibold" style="width: 40%;">Email:</td><td><strong>${escapeHtml(config.email_address)}</strong></td></tr>
                            <tr><td class="fw-semibold">Protocol:</td><td><span class="badge bg-info">${config.protocol.toUpperCase()}</span> ${config.ssl_enabled ? '<span class="badge bg-success ms-1">SSL</span>' : ''}</td></tr>
                            <tr><td class="fw-semibold">Host:</td><td>${escapeHtml(config.host)}</td></tr>
                            <tr><td class="fw-semibold">Port:</td><td>${config.port}</td></tr>
                            <tr><td class="fw-semibold">Username:</td><td>${escapeHtml(config.username)}</td></tr>
                            <tr><td class="fw-semibold">Folder:</td><td>${escapeHtml(config.folder || 'INBOX')}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Status & Statistics</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="fw-semibold">Status:</td><td><span class="badge bg-${statusColor}">${config.connection_status || 'Unknown'}</span></td></tr>
                            <tr><td class="fw-semibold">Active:</td><td><span class="badge bg-${config.is_active ? 'success' : 'secondary'}">${config.is_active ? 'Yes' : 'No'}</span></td></tr>
                            <tr><td class="fw-semibold">Last Test:</td><td>${lastTest}</td></tr>
                            <tr><td class="fw-semibold">Last Sync:</td><td>${lastSync}</td></tr>
                            <tr><td class="fw-semibold">Sync Count:</td><td><span class="text-success">${config.sync_count || 0} successful</span></td></tr>
                            ${config.failed_sync_count > 0 ? `<tr><td class="fw-semibold">Failed:</td><td><span class="text-danger">${config.failed_sync_count}</span></td></tr>` : ''}
                        </table>
                    </div>
                </div>
            </div>
        </div>
        ${config.connection_error ? `<div class="alert alert-danger"><strong>Connection Error:</strong> ${escapeHtml(config.connection_error)}</div>` : ''}
    `;
    const modal = new bootstrap.Modal(document.getElementById('viewConfigModal'));
    modal.show();
    
    // Ensure modal appears in front
    setTimeout(() => {
        const modalEl = document.getElementById('viewConfigModal');
        if (modalEl) {
            modalEl.style.zIndex = '100000';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.style.zIndex = '99999';
        }
    }, 100);
}

function editConfigFromView() {
    const configId = document.getElementById('viewConfigEditBtn').getAttribute('data-config-id');
    if (configId) {
        bootstrap.Modal.getInstance(document.getElementById('viewConfigModal')).hide();
        setTimeout(() => {
            editConfig(parseInt(configId));
        }, 300);
    }
}

// Edit config
window.editConfig = function(id) {
    const config = configData.find(c => c.id === id);
    if (!config) return;
    document.getElementById('editConfigId').value = config.id;
    document.getElementById('editEmailAddress').value = config.email_address;
    document.getElementById('editProtocol').value = config.protocol;
    document.getElementById('editHost').value = config.host;
    document.getElementById('editPort').value = config.port;
    if (document.getElementById('editEncryptionSelect')) {
        document.getElementById('editEncryptionSelect').value = config.ssl_enabled ? 'ssl' : 'tls';
    }
    document.getElementById('editUsername').value = config.username;
    document.getElementById('editPassword').value = '';
    document.getElementById('editFolder').value = config.folder || 'INBOX';
    document.getElementById('editIsActive').checked = config.is_active;
    updateEditPort();
    const modal = new bootstrap.Modal(document.getElementById('editEmailModal'));
    modal.show();
    
    // Ensure modal appears in front
    setTimeout(() => {
        const modalEl = document.getElementById('editEmailModal');
        if (modalEl) {
            modalEl.style.zIndex = '100000';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.style.zIndex = '99999';
        }
    }, 100);
}

// Delete config
async function deleteConfig(id) {
    const config = configData.find(c => c.id === id);
    const emailAddress = config?.email_address || 'this email configuration';
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Delete Email Configuration?',
        html: `<p>Are you sure you want to delete the configuration for:</p><p class="fw-bold text-primary">${emailAddress}</p><p class="text-danger small mt-2">This action cannot be undone!</p>`,
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    });
    if (!result.isConfirmed) return;
    Swal.fire({
        title: 'Deleting...',
        html: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    try {
        const res = await fetch(`/modules/incidents/email-config/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted Successfully!',
                text: data.message || 'Email configuration deleted successfully'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: data.message || 'Error deleting configuration'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: err.message || 'Network error. Please check your internet connection.'
        });
    }
}

window.deleteConfig = deleteConfig;

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('emailConfigForm');
    if (emailForm) {
        emailForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            try {
                const res = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Email configuration saved successfully'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save configuration'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Network error. Please check your internet connection.'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });
    }

    const editForm = document.getElementById('editEmailConfigForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const configId = document.getElementById('editConfigId').value;
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('_token', token);
            formData.append('email_address', document.getElementById('editEmailAddress').value);
            formData.append('protocol', document.getElementById('editProtocol').value);
            formData.append('host', document.getElementById('editHost').value);
            formData.append('port', document.getElementById('editPort').value);
            formData.append('encryption', document.getElementById('editEncryptionSelect').value);
            formData.append('username', document.getElementById('editUsername').value);
            const password = document.getElementById('editPassword').value;
            if (password) formData.append('password', password);
            formData.append('folder', document.getElementById('editFolder').value);
            formData.append('is_active', document.getElementById('editIsActive').checked ? '1' : '0');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
            try {
                const res = await fetch(`/modules/incidents/email-config/${configId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Email configuration updated successfully'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update configuration'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Network error. Please check your internet connection.'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection




