/**
 * Meeting Management Module JavaScript
 * Handles all meeting and minutes functionality
 */

$(document).ready(function() {
    // Configuration
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
    const ajaxUrl = window.meetingAjaxUrl || '/modules/meetings/ajax';
    
    // State
    let currentStep = 1;
    let currentMeetingId = null;
    let meetingData = {};
    let staffList = [];
    let categoriesList = [];
    let currentView = 'list';

    // Initialize
    function init() {
        loadDashboardStats();
        loadCategories();
        loadMeetings();
        loadRecentActivity();
        setupEventListeners();
        initializeDateTimePickers();
        initializeSelect2();
    }

    // Setup Event Listeners
    function setupEventListeners() {
        // Navigation
        $('#create-meeting-btn, #quick-create-meeting').on('click', () => openMeetingWizard());
        $('#manage-categories-btn, #add-category-btn').on('click', () => $('#categoriesModal').modal('show'));
        $('#create-minutes-btn, #quick-create-minutes').on('click', () => openMinutesModal());
        $('#pending-approvals-btn').on('click', () => loadPendingApprovals());
        $('#refresh-btn').on('click', refreshAll);

        // Wizard Navigation
        $('.wizard-step').on('click', function() {
            const step = $(this).data('step');
            if ($(this).hasClass('completed') || step <= currentStep) {
                goToStep(step);
            }
        });
        $('#next-step-btn').on('click', nextStep);
        $('#prev-step-btn').on('click', prevStep);
        $('#submit-meeting-btn').on('click', submitMeeting);

        // Save Step Buttons
        $('.save-step-btn').on('click', function() {
            saveStep($(this).data('step'));
        });

        // Edit Section Buttons
        $('.edit-section-btn').on('click', function() {
            goToStep($(this).data('step'));
        });

        // Participants
        $('#add-external-btn').on('click', addExternalParticipant);
        $(document).on('click', '.remove-external-btn', function() {
            $(this).closest('.external-participant').remove();
        });

        // Agenda
        $('#add-agenda-btn').on('click', addAgendaItem);
        $(document).on('click', '.remove-agenda-btn', function() {
            $(this).closest('.agenda-item').remove();
            reindexAgendaItems();
        });

        // Categories
        $('#categoryForm').on('submit', handleCategorySubmit);
        $(document).on('click', '.edit-category-btn', editCategory);
        $(document).on('click', '.delete-category-btn', deleteCategory);

        // Filters
        $('#filter-status, #filter-category').on('change', loadMeetings);
        $('#filter-search').on('keyup', debounce(loadMeetings, 300));

        // View Toggle
        $('#view-list').on('click', () => { currentView = 'list'; loadMeetings(); $('#view-list').addClass('active'); $('#view-grid').removeClass('active'); });
        $('#view-grid').on('click', () => { currentView = 'grid'; loadMeetings(); $('#view-grid').addClass('active'); $('#view-list').removeClass('active'); });

        // Meeting Actions
        $(document).on('click', '.view-meeting-btn', function() { viewMeeting($(this).data('id')); });
        $(document).on('click', '.edit-meeting-btn', function() { editMeeting($(this).data('id')); });
        $(document).on('click', '.delete-meeting-btn', function() { deleteMeeting($(this).data('id')); });
        $(document).on('click', '.approve-btn', function() { approveMeeting($(this).data('id')); });

        // Minutes
        $('#select-meeting-for-minutes').on('change', loadMeetingForMinutes);
        $('#has-previous-actions').on('change', function() {
            $('#previous-actions-container').toggleClass('d-none', !this.checked);
        });
        $('#add-previous-action-btn').on('click', addPreviousAction);
        $('#add-action-item-btn').on('click', addActionItem);
        $(document).on('click', '.remove-prev-action-btn', function() { $(this).closest('.previous-action').remove(); });
        $(document).on('click', '.remove-action-btn', function() { $(this).closest('.new-action').remove(); });
        $('.save-minutes-section').on('click', function() { saveMinutesSection($(this).data('section')); });
        $('#save-all-minutes-btn').on('click', saveAllMinutes);
        $('#finalize-minutes-btn').on('click', finalizeMinutes);
        $('#preview-minutes-btn').on('click', previewMinutes);

        // Approval
        $('#approve-meeting-btn').on('click', approveMeetingFinal);
        $('#reject-meeting-btn').on('click', rejectMeeting);
        $('#edit-meeting-btn').on('click', function() {
            $('#meetingReviewModal').modal('hide');
            editMeeting(currentMeetingId);
        });

        // Staff Select
        $('#staff-select').on('change', updateSelectedStaffList);
    }

    // Initialize Date/Time Pickers
    function initializeDateTimePickers() {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true
        });
        flatpickr('#filter-date', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            onChange: loadMeetings
        });
    }

    // Initialize Select2
    function initializeSelect2() {
        $('#staff-select').select2({
            placeholder: 'Select staff members',
            allowClear: true,
            dropdownParent: $('#meetingWizardModal')
        });
    }

    // Load Dashboard Stats
    function loadDashboardStats() {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_dashboard_stats' },
            success: function(response) {
                if (response.success) {
                    $('#stat-total-meetings').text(response.stats.total_meetings || 0);
                    $('#stat-upcoming').text(response.stats.upcoming || 0);
                    $('#stat-pending').text(response.stats.pending_approval || 0);
                    $('#stat-minutes-pending').text(response.stats.minutes_pending || 0);
                }
            }
        });
    }

    // Load Categories
    function loadCategories() {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_categories' },
            success: function(response) {
                if (response.success) {
                    categoriesList = response.categories;
                    renderCategoriesList();
                    populateCategorySelects();
                }
            }
        });
    }

    // Render Categories List
    function renderCategoriesList() {
        const container = $('#categories-list');
        const tbody = $('#categoriesTable tbody');
        container.empty();
        tbody.empty();

        if (categoriesList.length === 0) {
            container.html('<div class="p-3 text-muted">No categories found</div>');
            return;
        }

        categoriesList.forEach(cat => {
            container.append(`
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center category-filter" data-id="${cat.id}">
                    <span><i class="bx bx-folder me-2"></i>${cat.name}</span>
                    <span class="badge bg-primary rounded-pill">${cat.meetings_count || 0}</span>
                </a>
            `);

            tbody.append(`
                <tr>
                    <td>${escapeHtml(cat.name)}</td>
                    <td>${escapeHtml(cat.description || '-')}</td>
                    <td><span class="badge bg-info">${cat.meetings_count || 0}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning edit-category-btn" data-id="${cat.id}" data-name="${escapeHtml(cat.name)}" data-description="${escapeHtml(cat.description || '')}">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-category-btn" data-id="${cat.id}">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Category filter click
        $('.category-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter-category').val($(this).data('id')).trigger('change');
        });
    }

    // Populate Category Selects
    function populateCategorySelects() {
        const selects = $('select[name="category_id"], #filter-category');
        selects.each(function() {
            const select = $(this);
            const currentVal = select.val();
            select.find('option:not(:first)').remove();
            categoriesList.forEach(cat => {
                select.append(`<option value="${cat.id}">${cat.name}</option>`);
            });
            if (currentVal) select.val(currentVal);
        });
    }

    // Load Staff
    function loadStaff() {
        return $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_staff' },
            success: function(response) {
                if (response.success) {
                    staffList = response.staff;
                    populateStaffSelect();
                    populateApproverSelect();
                }
            }
        });
    }

    // Populate Staff Select
    function populateStaffSelect() {
        const select = $('#staff-select');
        select.empty();
        staffList.forEach(staff => {
            select.append(`<option value="${staff.id}">${staff.name} - ${staff.department || 'N/A'}</option>`);
        });
        select.trigger('change.select2');
    }

    // Populate Approver Select
    function populateApproverSelect() {
        const select = $('select[name="approver_id"]');
        select.find('option:not(:first)').remove();
        staffList.filter(s => s.is_approver).forEach(staff => {
            select.append(`<option value="${staff.id}">${staff.name} (${staff.role || 'Approver'})</option>`);
        });
    }

    // Update Selected Staff List
    function updateSelectedStaffList() {
        const selected = $('#staff-select').val() || [];
        const container = $('#selected-staff-list');
        container.empty();

        selected.forEach(id => {
            const staff = staffList.find(s => s.id == id);
            if (staff) {
                container.append(`
                    <div class="col-md-6 mb-2">
                        <div class="participant-card">
                            <strong>${escapeHtml(staff.name)}</strong>
                            <br><small class="text-muted">${escapeHtml(staff.department || 'N/A')} | ${escapeHtml(staff.email || '')}</small>
                        </div>
                    </div>
                `);
            }
        });
    }

    // Load Meetings
    function loadMeetings() {
        const filters = {
            status: $('#filter-status').val(),
            category_id: $('#filter-category').val(),
            date_range: $('#filter-date').val(),
            search: $('#filter-search').val()
        };

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_meetings', ...filters },
            success: function(response) {
                if (response.success) {
                    renderMeetings(response.meetings);
                }
            }
        });
    }

    // Render Meetings
    function renderMeetings(meetings) {
        const container = $('#meetings-container');
        container.empty();

        if (meetings.length === 0) {
            container.html('<div class="text-center py-5 text-muted"><i class="bx bx-calendar-x" style="font-size: 3rem;"></i><p>No meetings found</p></div>');
            return;
        }

        if (currentView === 'grid') {
            container.html('<div class="row"></div>');
            const row = container.find('.row');
            meetings.forEach(meeting => {
                row.append(renderMeetingCard(meeting));
            });
        } else {
            container.html('<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Title</th><th>Date & Time</th><th>Venue</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div>');
            const tbody = container.find('tbody');
            meetings.forEach(meeting => {
                tbody.append(renderMeetingRow(meeting));
            });
        }
    }

    // Render Meeting Card
    function renderMeetingCard(meeting) {
        return `
            <div class="col-md-4 mb-3">
                <div class="meeting-card card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-${getStatusColor(meeting.status)}">${meeting.status}</span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item view-meeting-btn" href="#" data-id="${meeting.id}"><i class="bx bx-show"></i> View</a></li>
                                    <li><a class="dropdown-item edit-meeting-btn" href="#" data-id="${meeting.id}"><i class="bx bx-edit"></i> Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger delete-meeting-btn" href="#" data-id="${meeting.id}"><i class="bx bx-trash"></i> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <h6 class="card-title">${escapeHtml(meeting.title)}</h6>
                        <p class="card-text small text-muted mb-2">
                            <i class="bx bx-calendar"></i> ${meeting.meeting_date} ${meeting.start_time}<br>
                            <i class="bx bx-map"></i> ${escapeHtml(meeting.venue || 'TBD')}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bx bx-user"></i> ${meeting.participants_count || 0} participants</small>
                            <span class="badge bg-secondary">${escapeHtml(meeting.category_name || 'Uncategorized')}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Render Meeting Row
    function renderMeetingRow(meeting) {
        return `
            <tr>
                <td>
                    <strong>${escapeHtml(meeting.title)}</strong>
                    ${meeting.has_minutes ? '<span class="badge bg-success ms-1">Minutes</span>' : ''}
                </td>
                <td>${meeting.meeting_date}<br><small class="text-muted">${meeting.start_time} - ${meeting.end_time}</small></td>
                <td>${escapeHtml(meeting.venue || 'TBD')}</td>
                <td><span class="badge bg-info">${escapeHtml(meeting.category_name || 'N/A')}</span></td>
                <td><span class="badge bg-${getStatusColor(meeting.status)}">${meeting.status}</span></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info view-meeting-btn" data-id="${meeting.id}" title="View"><i class="bx bx-show"></i></button>
                        <button class="btn btn-outline-warning edit-meeting-btn" data-id="${meeting.id}" title="Edit"><i class="bx bx-edit"></i></button>
                        ${meeting.status === 'pending_approval' ? `<button class="btn btn-outline-success approve-btn" data-id="${meeting.id}" title="Review"><i class="bx bx-check"></i></button>` : ''}
                        <button class="btn btn-outline-danger delete-meeting-btn" data-id="${meeting.id}" title="Delete"><i class="bx bx-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    }

    // Load Recent Activity
    function loadRecentActivity() {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_recent_activity' },
            success: function(response) {
                if (response.success) {
                    renderRecentActivity(response.activities);
                }
            }
        });
    }

    // Render Recent Activity
    function renderRecentActivity(activities) {
        const container = $('#recent-activity');
        container.empty();

        if (activities.length === 0) {
            container.html('<div class="text-muted">No recent activity</div>');
            return;
        }

        activities.forEach(activity => {
            container.append(`
                <div class="d-flex align-items-center mb-2">
                    <i class="bx ${getActivityIcon(activity.type)} text-primary me-2"></i>
                    <div class="flex-grow-1">
                        <small>${escapeHtml(activity.description)}</small>
                        <br><small class="text-muted">${activity.created_at}</small>
                    </div>
                </div>
            `);
        });
    }

    // Open Meeting Wizard
    function openMeetingWizard(meetingId = null) {
        currentMeetingId = meetingId;
        currentStep = 1;
        meetingData = {};
        
        // Reset form
        $('#meetingForm')[0].reset();
        $('#meeting_id').val(meetingId || '');
        $('#wizard-title').text(meetingId ? 'Edit Meeting' : 'Create Meeting');
        
        // Reset wizard steps
        $('.wizard-step').removeClass('active completed');
        $('.wizard-step[data-step="1"]').addClass('active');
        $('.wizard-content').addClass('d-none');
        $('#step-1').removeClass('d-none');
        
        // Reset buttons
        $('#prev-step-btn').addClass('d-none');
        $('#next-step-btn').removeClass('d-none');
        $('#submit-meeting-btn').addClass('d-none');

        // Load data
        loadStaff().then(() => {
            if (meetingId) {
                loadMeetingData(meetingId);
            }
        });

        $('#meetingWizardModal').modal('show');
    }

    // Load Meeting Data for Edit
    function loadMeetingData(meetingId) {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_meeting', meeting_id: meetingId },
            success: function(response) {
                if (response.success) {
                    const meeting = response.meeting;
                    meetingData = meeting;
                    
                    // Populate form
                    $('input[name="title"]').val(meeting.title);
                    $('select[name="category_id"]').val(meeting.category_id);
                    $('input[name="meeting_date"]').val(meeting.meeting_date);
                    $('input[name="start_time"]').val(meeting.start_time);
                    $('input[name="end_time"]').val(meeting.end_time);
                    $('input[name="venue"]').val(meeting.venue);
                    $('select[name="meeting_type"]').val(meeting.meeting_type);
                    $('textarea[name="description"]').val(meeting.description);

                    // Participants
                    if (meeting.staff_participants) {
                        $('#staff-select').val(meeting.staff_participants.map(p => p.id)).trigger('change');
                    }
                    
                    // External participants
                    if (meeting.external_participants) {
                        meeting.external_participants.forEach(p => {
                            addExternalParticipant();
                            const last = $('.external-participant').last();
                            last.find('input[name="external_name[]"]').val(p.name);
                            last.find('input[name="external_email[]"]').val(p.email);
                            last.find('input[name="external_phone[]"]').val(p.phone);
                            last.find('input[name="external_institution[]"]').val(p.institution);
                        });
                    }

                    // Agenda
                    if (meeting.agendas) {
                        meeting.agendas.forEach(agenda => {
                            addAgendaItem();
                            const last = $('.agenda-item').last();
                            last.find('input[name="agenda_title[]"]').val(agenda.title);
                            last.find('input[name="agenda_duration[]"]').val(agenda.duration);
                            last.find('select[name="agenda_presenter[]"]').val(agenda.presenter_id);
                            last.find('input[name="agenda_documents[]"]').val(agenda.documents);
                            last.find('textarea[name="agenda_description[]"]').val(agenda.description);
                        });
                    }
                }
            }
        });
    }

    // Go to Step
    function goToStep(step) {
        // Mark current step as completed if moving forward
        if (step > currentStep) {
            $(`.wizard-step[data-step="${currentStep}"]`).removeClass('active').addClass('completed');
        }

        currentStep = step;
        
        // Update wizard steps
        $('.wizard-step').removeClass('active');
        $(`.wizard-step[data-step="${step}"]`).addClass('active');
        
        // Show content
        $('.wizard-content').addClass('d-none');
        $(`#step-${step}`).removeClass('d-none');
        
        // Update buttons
        $('#prev-step-btn').toggleClass('d-none', step === 1);
        $('#next-step-btn').toggleClass('d-none', step === 4);
        $('#submit-meeting-btn').toggleClass('d-none', step !== 4);

        // Load review content if on step 4
        if (step === 4) {
            loadReviewContent();
        }
    }

    // Next Step
    function nextStep() {
        if (currentStep < 4) {
            // Validate current step
            if (!validateStep(currentStep)) {
                return;
            }
            goToStep(currentStep + 1);
        }
    }

    // Previous Step
    function prevStep() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }

    // Validate Step
    function validateStep(step) {
        let valid = true;
        
        if (step === 1) {
            const required = ['title', 'category_id', 'meeting_date', 'start_time', 'end_time', 'venue'];
            required.forEach(field => {
                const input = $(`[name="${field}"]`);
                if (!input.val()) {
                    input.addClass('is-invalid');
                    valid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });
        } else if (step === 2) {
            const staffSelected = ($('#staff-select').val() || []).length > 0;
            const externalCount = $('.external-participant').length;
            if (!staffSelected && externalCount === 0) {
                Swal.fire('Warning', 'Please add at least one participant', 'warning');
                valid = false;
            }
        } else if (step === 3) {
            if ($('.agenda-item').length === 0) {
                Swal.fire('Warning', 'Please add at least one agenda item', 'warning');
                valid = false;
            }
        }

        return valid;
    }

    // Save Step
    function saveStep(step) {
        if (!validateStep(step)) return;

        const formData = new FormData($('#meetingForm')[0]);
        formData.append('action', 'save_meeting_step');
        formData.append('step', step);
        
        // Add staff participants
        const staffIds = $('#staff-select').val() || [];
        staffIds.forEach(id => formData.append('staff_participants[]', id));

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.meeting_id) {
                        currentMeetingId = response.meeting_id;
                        $('#meeting_id').val(response.meeting_id);
                    }
                    Swal.fire('Saved!', `Step ${step} saved successfully`, 'success');
                    $(`.wizard-step[data-step="${step}"]`).addClass('completed');
                } else {
                    Swal.fire('Error', response.message || 'Failed to save', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to save step', 'error');
            }
        });
    }

    // Load Review Content
    function loadReviewContent() {
        // Basic Info
        let basicHtml = `
            <table class="table table-sm">
                <tr><td><strong>Title:</strong></td><td>${escapeHtml($('input[name="title"]').val())}</td></tr>
                <tr><td><strong>Category:</strong></td><td>${escapeHtml($('select[name="category_id"] option:selected').text())}</td></tr>
                <tr><td><strong>Date:</strong></td><td>${$('input[name="meeting_date"]').val()}</td></tr>
                <tr><td><strong>Time:</strong></td><td>${$('input[name="start_time"]').val()} - ${$('input[name="end_time"]').val()}</td></tr>
                <tr><td><strong>Venue:</strong></td><td>${escapeHtml($('input[name="venue"]').val())}</td></tr>
                <tr><td><strong>Type:</strong></td><td>${$('select[name="meeting_type"]').val()}</td></tr>
                <tr><td><strong>Description:</strong></td><td>${escapeHtml($('textarea[name="description"]').val()) || 'N/A'}</td></tr>
            </table>
        `;
        $('#review-basic-info').html(basicHtml);

        // Participants
        let participantsHtml = '<h6>Internal Staff:</h6><ul>';
        const selectedStaff = $('#staff-select').val() || [];
        selectedStaff.forEach(id => {
            const staff = staffList.find(s => s.id == id);
            if (staff) {
                participantsHtml += `<li>${escapeHtml(staff.name)} (${escapeHtml(staff.department || 'N/A')})</li>`;
            }
        });
        participantsHtml += '</ul>';

        const externals = [];
        $('.external-participant').each(function() {
            externals.push({
                name: $(this).find('input[name="external_name[]"]').val(),
                email: $(this).find('input[name="external_email[]"]').val(),
                phone: $(this).find('input[name="external_phone[]"]').val(),
                institution: $(this).find('input[name="external_institution[]"]').val()
            });
        });
        
        if (externals.length > 0) {
            participantsHtml += '<h6 class="mt-3">External Participants:</h6><ul>';
            externals.forEach(p => {
                participantsHtml += `<li>${escapeHtml(p.name)} - ${escapeHtml(p.institution || 'N/A')} (${escapeHtml(p.phone)})</li>`;
            });
            participantsHtml += '</ul>';
        }
        $('#review-participants').html(participantsHtml);

        // Agenda
        let agendaHtml = '<ol>';
        $('.agenda-item').each(function(index) {
            const title = $(this).find('input[name="agenda_title[]"]').val();
            const duration = $(this).find('input[name="agenda_duration[]"]').val();
            const presenter = $(this).find('select[name="agenda_presenter[]"] option:selected').text();
            agendaHtml += `<li><strong>${escapeHtml(title)}</strong> ${duration ? `(${duration})` : ''} ${presenter !== 'Select Presenter' ? `- ${presenter}` : ''}</li>`;
        });
        agendaHtml += '</ol>';
        $('#review-agenda').html(agendaHtml);
    }

    // Submit Meeting
    function submitMeeting() {
        if (!$('select[name="approver_id"]').val()) {
            Swal.fire('Error', 'Please select an approver', 'error');
            return;
        }

        Swal.fire({
            title: 'Submit Meeting?',
            text: 'This will send the meeting for approval. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Submit'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData($('#meetingForm')[0]);
                formData.append('action', 'submit_meeting');
                
                // Add all participants
                const staffIds = $('#staff-select').val() || [];
                staffIds.forEach(id => formData.append('staff_participants[]', id));

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'Meeting submitted for approval', 'success');
                            $('#meetingWizardModal').modal('hide');
                            refreshAll();
                        } else {
                            Swal.fire('Error', response.message || 'Failed to submit', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to submit meeting', 'error');
                    }
                });
            }
        });
    }

    // Add External Participant
    function addExternalParticipant() {
        const template = document.getElementById('external-participant-template');
        const clone = template.content.cloneNode(true);
        $('#external-participants-list').append(clone);
    }

    // Add Agenda Item
    function addAgendaItem() {
        const template = document.getElementById('agenda-item-template');
        const clone = template.content.cloneNode(true);
        const container = $('#agenda-items-list');
        container.append(clone);
        
        // Populate presenter select
        const presenterSelect = container.find('.agenda-item').last().find('.presenter-select');
        staffList.forEach(staff => {
            presenterSelect.append(`<option value="${staff.id}">${staff.name}</option>`);
        });

        reindexAgendaItems();
        initSortableAgenda();
    }

    // Reindex Agenda Items
    function reindexAgendaItems() {
        $('.agenda-item').each(function(index) {
            $(this).attr('data-index', index + 1);
        });
    }

    // Initialize Sortable Agenda
    function initSortableAgenda() {
        if (typeof Sortable !== 'undefined') {
            const el = document.getElementById('agenda-items-list');
            if (el && !el.sortableInstance) {
                el.sortableInstance = new Sortable(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: reindexAgendaItems
                });
            }
        }
    }

    // Category Submit
    function handleCategorySubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', formData.get('category_id') ? 'update_category' : 'create_category');

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#categoryForm')[0].reset();
                    $('#category_id').val('');
                    loadCategories();
                } else {
                    Swal.fire('Error', response.message || 'Failed to save category', 'error');
                }
            }
        });
    }

    // Edit Category
    function editCategory() {
        const btn = $(this);
        $('#category_id').val(btn.data('id'));
        $('input[name="name"]').val(btn.data('name'));
        $('input[name="description"]').val(btn.data('description'));
        $('#categoryForm button[type="submit"]').html('<i class="bx bx-save"></i> Update Category');
    }

    // Delete Category
    function deleteCategory() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Category?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: { _token: csrfToken, action: 'delete_category', category_id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'Category deleted', 'success');
                            loadCategories();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // View Meeting
    function viewMeeting(id) {
        currentMeetingId = id;
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_meeting', meeting_id: id },
            success: function(response) {
                if (response.success) {
                    renderMeetingReview(response.meeting);
                    $('#meetingReviewModal').modal('show');
                }
            }
        });
    }

    // Render Meeting Review
    function renderMeetingReview(meeting) {
        let html = `
            <div class="row">
                <div class="col-md-8">
                    <div class="review-section">
                        <h6><i class="bx bx-info-circle"></i> Meeting Details</h6>
                        <table class="table table-sm">
                            <tr><td width="150"><strong>Title:</strong></td><td>${escapeHtml(meeting.title)}</td></tr>
                            <tr><td><strong>Category:</strong></td><td>${escapeHtml(meeting.category_name || 'N/A')}</td></tr>
                            <tr><td><strong>Date:</strong></td><td>${meeting.meeting_date}</td></tr>
                            <tr><td><strong>Time:</strong></td><td>${meeting.start_time} - ${meeting.end_time}</td></tr>
                            <tr><td><strong>Venue:</strong></td><td>${escapeHtml(meeting.venue)}</td></tr>
                            <tr><td><strong>Type:</strong></td><td>${meeting.meeting_type}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusColor(meeting.status)}">${meeting.status}</span></td></tr>
                        </table>
                        ${meeting.description ? `<p><strong>Description:</strong><br>${escapeHtml(meeting.description)}</p>` : ''}
                    </div>

                    <div class="review-section">
                        <h6><i class="bx bx-list-check"></i> Agenda Items</h6>
                        <ol>
                            ${(meeting.agendas || []).map(a => `
                                <li class="mb-2">
                                    <strong>${escapeHtml(a.title)}</strong>
                                    ${a.duration ? `<span class="badge bg-secondary ms-2">${a.duration}</span>` : ''}
                                    ${a.presenter_name ? `<br><small class="text-muted">Presenter: ${escapeHtml(a.presenter_name)}</small>` : ''}
                                    ${a.description ? `<br><small>${escapeHtml(a.description)}</small>` : ''}
                                </li>
                            `).join('')}
                        </ol>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="review-section">
                        <h6><i class="bx bx-user"></i> Participants (${(meeting.staff_participants || []).length + (meeting.external_participants || []).length})</h6>
                        <h6 class="mt-3">Internal Staff:</h6>
                        <ul class="list-unstyled">
                            ${(meeting.staff_participants || []).map(p => `
                                <li><i class="bx bx-user text-primary"></i> ${escapeHtml(p.name)}</li>
                            `).join('')}
                        </ul>
                        ${(meeting.external_participants || []).length > 0 ? `
                            <h6 class="mt-3">External:</h6>
                            <ul class="list-unstyled">
                                ${meeting.external_participants.map(p => `
                                    <li><i class="bx bx-user-voice text-success"></i> ${escapeHtml(p.name)} (${escapeHtml(p.institution || 'N/A')})</li>
                                `).join('')}
                            </ul>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        $('#meeting-review-content').html(html);

        // Show/hide approval buttons based on status
        const canApprove = meeting.status === 'pending_approval';
        $('#approve-meeting-btn, #reject-meeting-btn').toggle(canApprove);
    }

    // Edit Meeting
    function editMeeting(id) {
        openMeetingWizard(id);
    }

    // Delete Meeting
    function deleteMeeting(id) {
        Swal.fire({
            title: 'Delete Meeting?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: { _token: csrfToken, action: 'delete_meeting', meeting_id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'Meeting deleted', 'success');
                            refreshAll();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // Approve Meeting (Show Review)
    function approveMeeting(id) {
        viewMeeting(id);
    }

    // Approve Meeting Final
    function approveMeetingFinal() {
        Swal.fire({
            title: 'Approve Meeting?',
            html: 'This will approve the meeting and send SMS notifications to all participants.<br><br><textarea id="approval-message" class="form-control" rows="3" placeholder="Custom message for SMS (optional)"></textarea>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve & Send SMS',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                const message = $('#approval-message').val();
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'approve_meeting',
                        meeting_id: currentMeetingId,
                        custom_message: message
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Approved!', 'Meeting approved and SMS sent to participants', 'success');
                            $('#meetingReviewModal').modal('hide');
                            refreshAll();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // Reject Meeting
    function rejectMeeting() {
        Swal.fire({
            title: 'Reject Meeting?',
            input: 'textarea',
            inputPlaceholder: 'Reason for rejection...',
            inputAttributes: { required: true },
            showCancelButton: true,
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'reject_meeting',
                        meeting_id: currentMeetingId,
                        reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rejected', 'Meeting has been rejected', 'info');
                            $('#meetingReviewModal').modal('hide');
                            refreshAll();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // Open Minutes Modal
    function openMinutesModal() {
        loadMeetingsWithoutMinutes();
        $('#minutes-form-container').addClass('d-none');
        $('#minutes-meeting-select').removeClass('d-none');
        $('#minutesModal').modal('show');
    }

    // Load Meetings Without Minutes
    function loadMeetingsWithoutMinutes() {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_meetings_without_minutes' },
            success: function(response) {
                if (response.success) {
                    const select = $('#select-meeting-for-minutes');
                    select.find('option:not(:first)').remove();
                    response.meetings.forEach(m => {
                        select.append(`<option value="${m.id}">${m.title} - ${m.meeting_date}</option>`);
                    });
                }
            }
        });
    }

    // Load Meeting for Minutes
    function loadMeetingForMinutes() {
        const meetingId = $(this).val();
        if (!meetingId) {
            $('#minutes-form-container').addClass('d-none');
            return;
        }

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'get_meeting_for_minutes', meeting_id: meetingId },
            success: function(response) {
                if (response.success) {
                    renderMinutesForm(response.meeting);
                    $('#minutes-form-container').removeClass('d-none');
                    $('#minutes-meeting-select').addClass('d-none');
                }
            }
        });
    }

    // Render Minutes Form
    function renderMinutesForm(meeting) {
        $('#minutes_meeting_id').val(meeting.id);
        
        // Meeting Info
        $('#minutes-meeting-info').html(`
            <strong>${escapeHtml(meeting.title)}</strong><br>
            <small>Date: ${meeting.meeting_date} | Time: ${meeting.start_time} - ${meeting.end_time} | Venue: ${escapeHtml(meeting.venue)}</small>
        `);

        // Attendance
        let attendanceHtml = '<div class="row">';
        (meeting.staff_participants || []).forEach(p => {
            attendanceHtml += `
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input attendance-check" type="checkbox" name="attendance[]" value="${p.id}" id="att-${p.id}">
                        <label class="form-check-label" for="att-${p.id}">${escapeHtml(p.name)}</label>
                    </div>
                </div>
            `;
        });
        (meeting.external_participants || []).forEach((p, i) => {
            attendanceHtml += `
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input attendance-check" type="checkbox" name="external_attendance[]" value="${i}" id="ext-att-${i}">
                        <label class="form-check-label" for="ext-att-${i}">${escapeHtml(p.name)} <span class="badge bg-info">External</span></label>
                    </div>
                </div>
            `;
        });
        attendanceHtml += '</div>';
        $('#attendance-list').html(attendanceHtml);

        // Agenda Minutes
        let agendaHtml = '';
        (meeting.agendas || []).forEach((agenda, index) => {
            agendaHtml += `
                <div class="agenda-minutes-item mb-4 p-3 border rounded">
                    <h6 class="text-primary">${index + 1}. ${escapeHtml(agenda.title)}</h6>
                    <div class="mb-2">
                        <label class="form-label">Discussion Notes</label>
                        <textarea name="agenda_discussion[${agenda.id}]" class="form-control" rows="3" placeholder="Enter discussion notes..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Resolution/Decision</label>
                        <textarea name="agenda_resolution[${agenda.id}]" class="form-control" rows="2" placeholder="Enter resolution or decision made..."></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-success btn-sm save-agenda-minutes" data-agenda-id="${agenda.id}">
                            <i class="bx bx-save"></i> Save This Agenda
                        </button>
                    </div>
                </div>
            `;
        });
        $('#agenda-minutes-list').html(agendaHtml);

        // Populate responsible selects
        populateResponsibleSelects(meeting);

        // Reinitialize pickers
        initializeDateTimePickers();
    }

    // Populate Responsible Selects
    function populateResponsibleSelects(meeting) {
        const allParticipants = [
            ...(meeting.staff_participants || []).map(p => ({ id: p.id, name: p.name, type: 'staff' })),
            ...(meeting.external_participants || []).map((p, i) => ({ id: `ext-${i}`, name: p.name, type: 'external' }))
        ];

        $('.responsible-select').each(function() {
            const select = $(this);
            select.find('option:not(:first)').remove();
            allParticipants.forEach(p => {
                select.append(`<option value="${p.id}">${p.name}${p.type === 'external' ? ' (External)' : ''}</option>`);
            });
        });
    }

    // Add Previous Action
    function addPreviousAction() {
        const template = document.getElementById('previous-action-template');
        const clone = template.content.cloneNode(true);
        $('#previous-actions-list').append(clone);
        
        // Populate responsible select
        const select = $('#previous-actions-list .previous-action').last().find('.responsible-select');
        staffList.forEach(staff => {
            select.append(`<option value="${staff.id}">${staff.name}</option>`);
        });
    }

    // Add Action Item
    function addActionItem() {
        const template = document.getElementById('action-item-template');
        const clone = template.content.cloneNode(true);
        $('#action-items-list').append(clone);
        
        // Populate responsible select
        const select = $('#action-items-list .new-action').last().find('.responsible-select');
        staffList.forEach(staff => {
            select.append(`<option value="${staff.id}">${staff.name}</option>`);
        });

        // Initialize datepicker
        flatpickr($('#action-items-list .new-action').last().find('.datepicker'), {
            dateFormat: 'Y-m-d'
        });
    }

    // Save Minutes Section
    function saveMinutesSection(section) {
        const meetingId = $('#minutes_meeting_id').val();
        const formData = new FormData($('#minutesForm')[0]);
        formData.append('action', 'save_minutes_section');
        formData.append('section', section);
        formData.append('meeting_id', meetingId);

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `${section} saved`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    // Save All Minutes
    function saveAllMinutes() {
        const formData = new FormData($('#minutesForm')[0]);
        formData.append('action', 'save_all_minutes');

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Saved!', 'All minutes saved successfully', 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    // Finalize Minutes
    function finalizeMinutes() {
        Swal.fire({
            title: 'Finalize Minutes?',
            text: 'This will mark the minutes as complete. You can still edit them later.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Finalize'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData($('#minutesForm')[0]);
                formData.append('action', 'finalize_minutes');

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'Minutes finalized', 'success');
                            $('#minutesModal').modal('hide');
                            refreshAll();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // Preview Minutes
    function previewMinutes() {
        const meetingId = $('#minutes_meeting_id').val();
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: { _token: csrfToken, action: 'preview_minutes', meeting_id: meetingId },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Minutes Preview',
                        html: response.html,
                        width: '80%',
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                }
            }
        });
    }

    // Load Pending Approvals
    function loadPendingApprovals() {
        $('#filter-status').val('pending_approval').trigger('change');
    }

    // Refresh All
    function refreshAll() {
        loadDashboardStats();
        loadCategories();
        loadMeetings();
        loadRecentActivity();
    }

    // Utility Functions
    function getStatusColor(status) {
        const colors = {
            'draft': 'secondary',
            'pending_approval': 'warning',
            'approved': 'success',
            'completed': 'info',
            'cancelled': 'danger'
        };
        return colors[status] || 'secondary';
    }

    function getActivityIcon(type) {
        const icons = {
            'meeting_created': 'bx-calendar-plus',
            'meeting_approved': 'bx-check-circle',
            'meeting_rejected': 'bx-x-circle',
            'minutes_created': 'bx-file-plus',
            'minutes_finalized': 'bx-check-double'
        };
        return icons[type] || 'bx-info-circle';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Initialize
    init();
});

