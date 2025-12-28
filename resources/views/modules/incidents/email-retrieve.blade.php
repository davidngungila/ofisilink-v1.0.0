@extends('layouts.app')

@section('title', 'Email Retrieval - Incident Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .email-config-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .email-item {
        border-left: 3px solid #007bff;
        transition: all 0.2s;
        cursor: pointer;
    }
    .email-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .email-item.unread {
        border-left-color: #28a745;
        background-color: #f8f9fa;
    }
    .email-item.read {
        border-left-color: #6c757d;
    }
    .email-preview {
        max-height: 200px;
        overflow-y: auto;
    }
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    
    /* Ensure modals and popups appear in front */
    .modal {
        z-index: 100000 !important;
    }
    .modal-backdrop {
        z-index: 99999 !important;
    }
    .modal-dialog {
        z-index: 100001 !important;
    }
    .swal2-container {
        z-index: 1000000 !important;
    }
    .swal2-popup {
        z-index: 1000001 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-info" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-envelope-open me-2"></i>Email Retrieval
                            </h3>
                            <p class="mb-0 text-white-50">Retrieve and view emails from configured accounts</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" onclick="refreshAllEmails()">
                                <i class="bx bx-refresh me-1"></i>Refresh All
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bx bx-envelope fs-1 text-info"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="total-emails-count">0</h3>
                    <small class="text-muted fw-semibold">Total Emails</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bx bx-envelope-open fs-1 text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="unread-emails-count">0</h3>
                    <small class="text-muted fw-semibold">Unread Emails</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bx bx-check-circle fs-1 text-primary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'connected')->count() }}</h3>
                    <small class="text-muted fw-semibold">Connected Accounts</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bx bx-time fs-1 text-warning"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="last-refresh-time">Never</h3>
                    <small class="text-muted fw-semibold">Last Refresh</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Accounts Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm email-config-card">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-envelope-open me-2 text-info"></i>Retrieve Emails
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0 small">Filter:</label>
                        <select class="form-select form-select-sm" id="fetchModeSelect" style="width: auto;">
                            <option value="unseen">Unread Emails</option>
                            <option value="recent">Recent Emails (Last 7 days)</option>
                            <option value="all">All Emails (Last 30 days)</option>
                        </select>
                        <label class="form-label mb-0 small ms-2">Limit:</label>
                        <input type="number" class="form-control form-control-sm" id="emailLimit" value="50" min="1" max="200" style="width: 100px;" placeholder="50">
                    </div>
                </div>
                <div class="card-body">
                    @php
                    $activeConfigs = $configs->where('is_active', true)->where('connection_status', 'connected');
                    @endphp
                    
                    @if($activeConfigs->count() > 0)
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-3" id="emailTabs" role="tablist">
                        @foreach($activeConfigs as $index => $config)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                    id="tab-{{ $config->id }}" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#email-tab-{{ $config->id }}" 
                                    type="button" 
                                    role="tab"
                                    onclick="loadEmailsForAccount({{ $config->id }})">
                                <i class="bx bx-envelope me-1"></i>
                                {{ $config->email_address }}
                                <span class="badge bg-danger ms-1" id="email-count-badge-{{ $config->id }}">0</span>
                                <span class="spinner-border spinner-border-sm ms-1 d-none" id="loading-spinner-{{ $config->id }}" style="width: 0.8rem; height: 0.8rem;"></span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="emailTabsContent">
                        @foreach($activeConfigs as $index => $config)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                             id="email-tab-{{ $config->id }}" 
                             role="tabpanel"
                             data-config-id="{{ $config->id }}"
                             data-email="{{ $config->email_address }}">
                            <div id="emails-container-{{ $config->id }}" class="emails-container">
                                <div class="text-center text-muted py-5">
                                    <div class="spinner-border text-info mb-3" role="status" id="initial-loading-{{ $config->id }}">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0" id="loading-text-{{ $config->id }}">
                                        <i class="bx bx-envelope me-2"></i>Loading emails for <strong>{{ $config->email_address }}</strong>...
                                    </p>
                                    <small class="text-muted d-block mt-2">This may take a few seconds</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-envelope fs-1"></i>
                        <p class="mt-2 mb-0">No active and connected email accounts found.</p>
                        <a href="{{ route('modules.incidents.email.config') }}" class="btn btn-info mt-3">
                            <i class="bx bx-plus me-1"></i>Add Email Account
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let allEmails = {};
    
    // Ensure all modals and popups appear in front
    (function() {
        document.addEventListener('show.bs.modal', function(e) {
            e.target.style.zIndex = '100000';
        });
        document.addEventListener('shown.bs.modal', function(e) {
            e.target.style.zIndex = '100000';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.style.zIndex = '99999';
        });
        if (typeof Swal !== 'undefined') {
            const originalFire = Swal.fire;
            Swal.fire = function(...args) {
                const result = originalFire.apply(this, args);
                setTimeout(() => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) swalContainer.style.zIndex = '1000000';
                }, 10);
                return result;
            };
        }
    })();

    function loadEmailsForAccount(configId) {
        const container = document.getElementById(`emails-container-${configId}`);
        const spinner = document.getElementById(`loading-spinner-${configId}`);
        const badge = document.getElementById(`email-count-badge-${configId}`);
        const initialLoading = document.getElementById(`initial-loading-${configId}`);
        const loadingText = document.getElementById(`loading-text-${configId}`);
        const fetchMode = document.getElementById('fetchModeSelect').value;
        const limit = parseInt(document.getElementById('emailLimit').value) || 50;

        if (spinner) spinner.classList.remove('d-none');
        if (initialLoading) initialLoading.style.display = 'block';
        if (loadingText) loadingText.style.display = 'block';

        const timeoutId = setTimeout(() => {
            container.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bx bx-time me-2"></i>Loading is taking longer than expected. Please wait...
                </div>
            `;
        }, 15000);

        const url = `/modules/incidents/email-config/${configId}/live-emails?limit=${limit}&fetch_mode=${fetchMode}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || `HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (spinner) spinner.classList.add('d-none');
            if (initialLoading) initialLoading.style.display = 'none';
            if (loadingText) loadingText.style.display = 'none';

            if (data.success) {
                if (data.emails && data.emails.length > 0) {
                    allEmails[configId] = data.emails;
                    displayEmails(configId, data.emails);
                    if (badge) badge.textContent = data.emails.length;
                    updateStatistics();
                    updateLastRefreshTime();
                } else {
                    // No emails found - provide helpful message with debugging info
                    container.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i><strong>No emails found</strong>
                            <p class="mb-2 mt-2">No emails found for the selected filter mode: <strong>${fetchMode}</strong></p>
                            <p class="mb-2 small text-muted">${data.message || ''}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('fetchModeSelect').value='all'; loadEmailsForAccount(${configId})">
                                    <i class="bx bx-envelope me-1"></i>Try All Emails (Last 90 days)
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="document.getElementById('fetchModeSelect').value='recent'; loadEmailsForAccount(${configId})">
                                    <i class="bx bx-time me-1"></i>Try Recent Emails (Last 7 days)
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="document.getElementById('fetchModeSelect').value='unseen'; loadEmailsForAccount(${configId})">
                                    <i class="bx bx-envelope-open me-1"></i>Try Unread Emails
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadEmailsForAccount(${configId})">
                                    <i class="bx bx-refresh me-1"></i>Retry
                                </button>
                            </div>
                            <p class="mt-3 mb-0 small text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> If emails exist but aren't showing, they may be outside the date range or already marked as read.
                            </p>
                        </div>
                    `;
                    if (badge) badge.textContent = '0';
                }
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i><strong>Error:</strong> ${data.message || 'No emails found.'}
                        <br><small class="text-muted mt-2 d-block">
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadEmailsForAccount(${configId})">
                                <i class="bx bx-refresh"></i> Retry
                            </button>
                        </small>
                    </div>
                `;
                if (badge) badge.textContent = '0';
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            if (spinner) spinner.classList.add('d-none');
            if (initialLoading) initialLoading.style.display = 'none';
            if (loadingText) loadingText.style.display = 'none';

            let errorMessage = 'An error occurred while loading emails.';
            try {
                const errorJson = JSON.parse(error.message);
                errorMessage = errorJson.message || errorMessage;
            } catch (e) {
                if (error.message.includes('Too many login failures')) {
                    errorMessage = 'Too many login failures. The email account may be temporarily locked.';
                } else if (error.message.includes('timeout') || error.message.includes('Maximum execution time')) {
                    errorMessage = 'Request timed out. The mailbox may be too large. Try reducing the limit (e.g., 20 instead of 50) or use "Recent Emails" mode.';
                } else if (error.message.length > 0 && error.message.length < 200) {
                    errorMessage = error.message;
                }
            }

            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i><strong>Error:</strong> ${escapeHtml(errorMessage)}
                    <br><small class="text-muted mt-2 d-block">
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadEmailsForAccount(${configId})">
                                <i class="bx bx-refresh"></i> Retry
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('emailLimit').value=20; loadEmailsForAccount(${configId})">
                                <i class="bx bx-filter"></i> Try with Limit 20
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="document.getElementById('fetchModeSelect').value='recent'; loadEmailsForAccount(${configId})">
                                <i class="bx bx-time"></i> Try Recent Emails
                            </button>
                        </div>
                    </small>
                </div>
            `;
            if (badge) badge.textContent = '0';
        });
    }

    function displayEmails(configId, emails) {
        const container = document.getElementById(`emails-container-${configId}`);
        
        if (emails.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>No emails found for this account.
                </div>
            `;
            return;
        }

        let html = '<div class="row g-3">';
        emails.forEach((email, index) => {
            const isUnread = email.seen === false || email.seen === 0;
            const emailClass = isUnread ? 'unread' : 'read';
            const date = new Date(email.date || email.received_at);
            const formattedDate = date.toLocaleString();
            
            html += `
                <div class="col-12">
                    <div class="card email-item ${emailClass}" onclick="viewEmailDetails(${configId}, ${index})">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">${escapeHtml(email.subject || '(No Subject)')}</h6>
                                        ${isUnread ? '<span class="badge bg-success">New</span>' : ''}
                                    </div>
                                    <div class="text-muted small mb-2">
                                        <i class="bx bx-user me-1"></i><strong>From:</strong> ${escapeHtml(email.from_name || email.from_email || 'Unknown')}
                                        <span class="ms-3"><i class="bx bx-time me-1"></i>${formattedDate}</span>
                                    </div>
                                    <div class="email-preview text-muted small">
                                        ${escapeHtml((email.body_preview || email.body || '').substring(0, 200))}${(email.body || '').length > 200 ? '...' : ''}
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); viewEmailDetails(${configId}, ${index})">
                                        <i class="bx bx-show"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function viewEmailDetails(configId, emailIndex) {
        const email = allEmails[configId][emailIndex];
        if (!email) return;

        const date = new Date(email.date || email.received_at);
        const formattedDate = date.toLocaleString();

        Swal.fire({
            title: email.subject || '(No Subject)',
            html: `
                <div class="text-start">
                    <p><strong>From:</strong> ${escapeHtml(email.from_name || email.from_email || 'Unknown')}</p>
                    <p><strong>To:</strong> ${escapeHtml(email.to || 'N/A')}</p>
                    <p><strong>Date:</strong> ${formattedDate}</p>
                    <hr>
                    <div style="max-height: 400px; overflow-y: auto; text-align: left; white-space: pre-wrap;">${escapeHtml(email.body || 'No content')}</div>
                    ${email.attachments && email.attachments.length > 0 ? `
                        <hr>
                        <p><strong>Attachments:</strong></p>
                        <ul>
                            ${email.attachments.map(att => `<li>${escapeHtml(att.name || att.filename || 'Unknown')}</li>`).join('')}
                        </ul>
                    ` : ''}
                </div>
            `,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Close'
        });
    }

    function refreshAllEmails() {
        const activeTabs = document.querySelectorAll('#emailTabsContent .tab-pane.active');
        activeTabs.forEach(tab => {
            const configId = tab.getAttribute('data-config-id');
            if (configId) {
                loadEmailsForAccount(parseInt(configId));
            }
        });
    }

    function updateStatistics() {
        let total = 0;
        let unread = 0;
        
        Object.values(allEmails).forEach(emails => {
            total += emails.length;
            unread += emails.filter(e => e.seen === false || e.seen === 0).length;
        });
        
        document.getElementById('total-emails-count').textContent = total;
        document.getElementById('unread-emails-count').textContent = unread;
    }

    function updateLastRefreshTime() {
        const now = new Date();
        document.getElementById('last-refresh-time').textContent = now.toLocaleTimeString();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Load emails for the first active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('#emailTabsContent .tab-pane.active');
        if (firstTab) {
            const configId = firstTab.getAttribute('data-config-id');
            if (configId) {
                loadEmailsForAccount(parseInt(configId));
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            refreshAllEmails();
        }, 30000);
    });

    // Listen for fetch mode and limit changes
    document.getElementById('fetchModeSelect').addEventListener('change', function() {
        const activeTab = document.querySelector('#emailTabsContent .tab-pane.active');
        if (activeTab) {
            const configId = activeTab.getAttribute('data-config-id');
            if (configId) {
                loadEmailsForAccount(parseInt(configId));
            }
        }
    });

    document.getElementById('emailLimit').addEventListener('change', function() {
        const activeTab = document.querySelector('#emailTabsContent .tab-pane.active');
        if (activeTab) {
            const configId = activeTab.getAttribute('data-config-id');
            if (configId) {
                loadEmailsForAccount(parseInt(configId));
            }
        }
    });
</script>
@endpush
@endsection




