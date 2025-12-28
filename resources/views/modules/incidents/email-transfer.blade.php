@extends('layouts.app')

@section('title', 'Email Transfer to Incidents - Incident Management')

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
    }
    .email-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .email-item.selected {
        border-left-color: #28a745;
        background-color: #f0f9ff;
    }
    .email-item.unread {
        border-left-color: #ffc107;
    }
    .email-preview {
        max-height: 150px;
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
    .transfer-actions {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 2px solid #dee2e6;
        padding: 15px;
        z-index: 100;
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
            <div class="card border-0 shadow-lg bg-success" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-transfer me-2"></i>Email Transfer to Incidents
                            </h3>
                            <p class="mb-0 text-white-50">Select and transfer emails to create incidents</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" onclick="transferSelectedEmails()" id="transferBtn" disabled>
                                <i class="bx bx-transfer me-1"></i>Transfer Selected (<span id="selected-count">0</span>)
                            </button>
                            <button class="btn btn-light" onclick="refreshAllEmails()">
                                <i class="bx bx-refresh me-1"></i>Refresh
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
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bx bx-envelope fs-1 text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="total-emails-count">0</h3>
                    <small class="text-muted fw-semibold">Total Emails</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bx bx-check-square fs-1 text-warning"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="selected-emails-count">0</h3>
                    <small class="text-muted fw-semibold">Selected</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bx bx-check-circle fs-1 text-info"></i>
                    </div>
                    <h3 class="mb-1 fw-bold" id="transferred-count">0</h3>
                    <small class="text-muted fw-semibold">Transferred Today</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bx bx-envelope-open fs-1 text-primary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'connected')->count() }}</h3>
                    <small class="text-muted fw-semibold">Connected Accounts</small>
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
                        <i class="bx bx-transfer me-2 text-success"></i>Select Emails to Transfer
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllEmails" onchange="toggleSelectAll()">
                            <label class="form-check-label" for="selectAllEmails">
                                Select All
                            </label>
                        </div>
                        <select class="form-select form-select-sm" id="fetchModeSelect" style="width: auto;">
                            <option value="unseen">Unread Emails</option>
                            <option value="recent">Recent Emails</option>
                            <option value="all">All Emails</option>
                        </select>
                        <input type="number" class="form-control form-control-sm" id="emailLimit" value="50" min="1" max="200" style="width: 100px;" placeholder="Limit">
                    </div>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
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
                                    <div class="spinner-border text-success mb-3" role="status" id="initial-loading-{{ $config->id }}">
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
                        <a href="{{ route('modules.incidents.email.config') }}" class="btn btn-success mt-3">
                            <i class="bx bx-plus me-1"></i>Add Email Account
                        </a>
                    </div>
                    @endif
                </div>
                <!-- Transfer Actions Bar -->
                <div class="transfer-actions d-none" id="transferActionsBar">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><span id="selected-count-bar">0</span> email(s) selected</strong>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                                <i class="bx bx-x me-1"></i>Clear Selection
                            </button>
                            <button class="btn btn-success btn-sm" onclick="transferSelectedEmails()">
                                <i class="bx bx-transfer me-1"></i>Transfer to Incidents
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Incident Details Modal -->
<div class="modal fade" id="editIncidentModal" tabindex="-1" aria-labelledby="editIncidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="editIncidentModalLabel">
                    <i class="bx bx-edit me-2"></i>Edit Incident Details Before Transfer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editIncidentForm">
                    <input type="hidden" id="editEmailKey" name="email_key">
                    <input type="hidden" id="editConfigId" name="config_id">
                    <input type="hidden" id="editEmailIndex" name="email_index">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="editTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTitle" name="title" required maxlength="255">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="editDescription" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="editDescription" name="description" rows="6" required></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editPriority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="editPriority" name="priority" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editCategory" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCategory" name="category" required>
                                <option value="technical">Technical</option>
                                <option value="hr">HR</option>
                                <option value="facilities">Facilities</option>
                                <option value="security">Security</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editReporterName" class="form-label">Reporter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editReporterName" name="reporter_name" required maxlength="255">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editReporterEmail" class="form-label">Reporter Email</label>
                            <input type="email" class="form-control" id="editReporterEmail" name="reporter_email">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editReporterPhone" class="form-label">Reporter Phone</label>
                            <input type="text" class="form-control" id="editReporterPhone" name="reporter_phone">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editAssignedTo" class="form-label">Assign To</label>
                            <select class="form-select" id="editAssignedTo" name="assigned_to">
                                <option value="">-- Not Assigned --</option>
                                @foreach($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}">{{ $staffMember->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editDueDate" name="due_date">
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Review and edit all details before transferring to incidents. All fields marked with <span class="text-danger">*</span> are required.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="confirmTransferFromModal()">
                    <i class="bx bx-transfer me-1"></i>Transfer to Incident
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let allEmails = {};
    let selectedEmails = new Set();
    
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

            if (data.success && data.emails) {
                allEmails[configId] = data.emails;
                displayEmails(configId, data.emails);
                if (badge) badge.textContent = data.emails.length;
                updateStatistics();
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>No emails found.
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
                } else if (error.message.length > 0 && error.message.length < 200) {
                    errorMessage = error.message;
                }
            }

            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i><strong>Error:</strong> ${escapeHtml(errorMessage)}
                    <br><small class="text-muted mt-2 d-block">
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadEmailsForAccount(${configId})">
                            <i class="bx bx-refresh"></i> Retry
                        </button>
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
            const emailClass = isUnread ? 'unread' : '';
            const date = new Date(email.date || email.received_at);
            const formattedDate = date.toLocaleString();
            const emailKey = `${configId}-${index}`;
            const isSelected = selectedEmails.has(emailKey);
            
            html += `
                <div class="col-12">
                    <div class="card email-item ${emailClass} ${isSelected ? 'selected' : ''}" id="email-${emailKey}">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="form-check me-3 mt-1">
                                    <input class="form-check-input email-checkbox" 
                                           type="checkbox" 
                                           id="checkbox-${emailKey}"
                                           data-config-id="${configId}"
                                           data-email-index="${index}"
                                           ${isSelected ? 'checked' : ''}
                                           onchange="toggleEmailSelection('${emailKey}')">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">${escapeHtml(email.subject || '(No Subject)')}</h6>
                                            <div class="text-muted small">
                                                <i class="bx bx-user me-1"></i><strong>From:</strong> ${escapeHtml(email.from_name || email.from_email || 'Unknown')}
                                                <span class="ms-3"><i class="bx bx-time me-1"></i>${formattedDate}</span>
                                            </div>
                                        </div>
                                        ${isUnread ? '<span class="badge bg-warning">New</span>' : ''}
                                    </div>
                                    <div class="email-preview text-muted small">
                                        ${escapeHtml((email.body_preview || email.body || '').substring(0, 150))}${(email.body || '').length > 150 ? '...' : ''}
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewEmailDetails(${configId}, ${index})">
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

    function toggleEmailSelection(emailKey) {
        const checkbox = document.getElementById(`checkbox-${emailKey}`);
        const emailItem = document.getElementById(`email-${emailKey}`);
        
        if (checkbox.checked) {
            selectedEmails.add(emailKey);
            emailItem.classList.add('selected');
        } else {
            selectedEmails.delete(emailKey);
            emailItem.classList.remove('selected');
        }
        
        updateSelectionUI();
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAllEmails');
        const checkboxes = document.querySelectorAll('.email-checkbox');
        const activeTab = document.querySelector('#emailTabsContent .tab-pane.active');
        const configId = activeTab ? activeTab.getAttribute('data-config-id') : null;
        
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            const emailIndex = cb.getAttribute('data-email-index');
            const emailKey = `${configId}-${emailIndex}`;
            
            if (selectAll.checked) {
                selectedEmails.add(emailKey);
                document.getElementById(`email-${emailKey}`)?.classList.add('selected');
            } else {
                selectedEmails.delete(emailKey);
                document.getElementById(`email-${emailKey}`)?.classList.remove('selected');
            }
        });
        
        updateSelectionUI();
    }

    function clearSelection() {
        selectedEmails.clear();
        document.querySelectorAll('.email-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.email-item').forEach(item => item.classList.remove('selected'));
        document.getElementById('selectAllEmails').checked = false;
        updateSelectionUI();
    }

    function updateSelectionUI() {
        const count = selectedEmails.size;
        document.getElementById('selected-count').textContent = count;
        document.getElementById('selected-emails-count').textContent = count;
        document.getElementById('selected-count-bar').textContent = count;
        
        const transferBtn = document.getElementById('transferBtn');
        const transferBar = document.getElementById('transferActionsBar');
        
        if (count > 0) {
            transferBtn.disabled = false;
            transferBar.classList.remove('d-none');
        } else {
            transferBtn.disabled = true;
            transferBar.classList.add('d-none');
        }
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
                </div>
            `,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            showDenyButton: true,
            denyButtonText: 'Transfer to Incident',
            denyButtonColor: '#28a745'
        }).then((result) => {
            if (result.isDenied) {
                const emailKey = `${configId}-${emailIndex}`;
                selectedEmails.add(emailKey);
                document.getElementById(`checkbox-${emailKey}`).checked = true;
                document.getElementById(`email-${emailKey}`).classList.add('selected');
                updateSelectionUI();
                showEditModalForEmail(emailKey);
            }
        });
    }

    function transferSelectedEmails() {
        if (selectedEmails.size === 0) {
            Swal.fire('No Selection', 'Please select at least one email to transfer.', 'warning');
            return;
        }

        // If only one email is selected, show edit modal
        if (selectedEmails.size === 1) {
            const emailKey = Array.from(selectedEmails)[0];
            showEditModalForEmail(emailKey);
        } else {
            // For multiple emails, show confirmation and process one by one
            Swal.fire({
                title: 'Transfer Multiple Emails?',
                html: `You are about to transfer <strong>${selectedEmails.size}</strong> email(s) to incidents. You will be able to edit each email before transfer.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    transferEmailsOneByOne();
                }
            });
        }
    }
    
    function transferEmailsOneByOne() {
        const emailKeys = Array.from(selectedEmails);
        if (emailKeys.length === 0) return;
        
        // Process first email
        showEditModalForEmail(emailKeys[0], emailKeys.slice(1));
    }
    
    let pendingEmailKeys = [];
    
    function showEditModalForEmail(emailKey, remainingKeys = []) {
        const [configId, emailIndex] = emailKey.split('-');
        const email = allEmails[configId][emailIndex];
        
        if (!email) {
            Swal.fire('Error', 'Email not found.', 'error');
            return;
        }
        
        // Store remaining keys for batch processing
        pendingEmailKeys = remainingKeys;
        
        // Populate modal with email data
        const cleanBody = cleanEmailBody(email.body || '');
        const priority = determinePriority(email.subject || '', cleanBody);
        const category = determineCategory(email.subject || '', cleanBody);
        
        document.getElementById('editEmailKey').value = emailKey;
        document.getElementById('editConfigId').value = configId;
        document.getElementById('editEmailIndex').value = emailIndex;
        document.getElementById('editTitle').value = email.subject || '(No Subject)';
        document.getElementById('editDescription').value = cleanBody || 'No description';
        document.getElementById('editPriority').value = priority;
        document.getElementById('editCategory').value = category;
        document.getElementById('editReporterName').value = email.from_name || email.from_email || 'Unknown';
        document.getElementById('editReporterEmail').value = email.from_email || '';
        document.getElementById('editReporterPhone').value = '';
        document.getElementById('editAssignedTo').value = '';
        document.getElementById('editDueDate').value = '';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editIncidentModal'));
        modal.show();
    }
    
    function confirmTransferFromModal() {
        const form = document.getElementById('editIncidentForm');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Get form data
        const formData = new FormData(form);
        const emailKey = formData.get('email_key');
        const [configId, emailIndex] = emailKey.split('-');
        const email = allEmails[configId][emailIndex];
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('editIncidentModal'));
        modal.hide();
        
        // Show loading
        Swal.fire({
            title: 'Transferring...',
            text: 'Please wait while we create the incident.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Prepare incident data
        const incidentData = new FormData();
        incidentData.append('title', formData.get('title'));
        incidentData.append('description', formData.get('description'));
        incidentData.append('priority', formData.get('priority'));
        incidentData.append('category', formData.get('category'));
        incidentData.append('reporter_name', formData.get('reporter_name'));
        if (formData.get('reporter_email')) {
            incidentData.append('reporter_email', formData.get('reporter_email'));
        }
        if (formData.get('reporter_phone')) {
            incidentData.append('reporter_phone', formData.get('reporter_phone'));
        }
        if (formData.get('assigned_to')) {
            incidentData.append('assigned_to', formData.get('assigned_to'));
        }
        if (formData.get('due_date')) {
            incidentData.append('due_date', formData.get('due_date'));
        }
        incidentData.append('source', 'email');
        
        // Transfer incident
        fetch('/modules/incidents', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: incidentData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.incident) {
                // Remove from selected
                selectedEmails.delete(emailKey);
                document.getElementById(`checkbox-${emailKey}`).checked = false;
                document.getElementById(`email-${emailKey}`)?.classList.remove('selected');
                updateSelectionUI();
                
                // Process next email if any
                if (pendingEmailKeys.length > 0) {
                    Swal.close();
                    showEditModalForEmail(pendingEmailKeys[0], pendingEmailKeys.slice(1));
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Transfer Complete',
                        text: 'Incident created successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload emails
                        const activeTab = document.querySelector('#emailTabsContent .tab-pane.active');
                        if (activeTab) {
                            const configId = activeTab.getAttribute('data-config-id');
                            if (configId) {
                                loadEmailsForAccount(parseInt(configId));
                            }
                        }
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Transfer Failed',
                    text: data.message || 'Failed to create incident.'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Transfer Failed',
                text: 'An error occurred while creating the incident.'
            });
        });
    }


    function cleanEmailBody(body) {
        if (!body) return '';
        // Remove email headers and signatures
        return body.replace(/^>.*$/gm, '').replace(/^On.*wrote:.*$/gm, '').trim();
    }

    function determinePriority(subject, body) {
        const text = (subject + ' ' + body).toLowerCase();
        if (text.includes('urgent') || text.includes('critical') || text.includes('emergency')) {
            return 'High';
        } else if (text.includes('important') || text.includes('asap')) {
            return 'Medium';
        }
        return 'Low';
    }

    function determineCategory(subject, body) {
        const text = (subject + ' ' + body).toLowerCase();
        if (text.includes('technical') || text.includes('system') || text.includes('server') || text.includes('it')) {
            return 'technical';
        } else if (text.includes('hr') || text.includes('human resource') || text.includes('employee')) {
            return 'hr';
        } else if (text.includes('facilities') || text.includes('facility') || text.includes('building')) {
            return 'facilities';
        } else if (text.includes('security') || text.includes('safety')) {
            return 'security';
        }
        return 'other';
    }

    function refreshAllEmails() {
        const activeTab = document.querySelector('#emailTabsContent .tab-pane.active');
        if (activeTab) {
            const configId = activeTab.getAttribute('data-config-id');
            if (configId) {
                loadEmailsForAccount(parseInt(configId));
            }
        }
    }

    function updateStatistics() {
        let total = 0;
        Object.values(allEmails).forEach(emails => {
            total += emails.length;
        });
        document.getElementById('total-emails-count').textContent = total;
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

