<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bx bx-time-five text-primary"></i> Work Schedules
            </h5>
            <button type="button" class="btn btn-primary" onclick="openScheduleModal()">
                <i class="bx bx-plus me-1"></i> Add Schedule
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="schedulesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Time</th>
                                <th>Work Hours</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="schedulesList">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
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
function loadSchedules() {
    // Load schedules
    const schedules = settingsData.schedules || [];
    displaySchedules(schedules);
}

function displaySchedules(schedules) {
    const tbody = document.getElementById('schedulesList');
    if (schedules.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No schedules found</td></tr>';
        return;
    }
    
    tbody.innerHTML = schedules.map(schedule => `
        <tr>
            <td>${schedule.name}</td>
            <td><code>${schedule.code}</code></td>
            <td>${schedule.start_time} - ${schedule.end_time}</td>
            <td>${schedule.work_hours} hrs</td>
            <td>${schedule.location?.name || 'All'}</td>
            <td>${schedule.department?.name || 'All'}</td>
            <td>${schedule.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editSchedule(${schedule.id})">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(${schedule.id})">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openScheduleModal() {
    alert('Schedule modal will be implemented');
}

function editSchedule(id) {
    alert('Edit schedule: ' + id);
}

function deleteSchedule(id) {
    Swal.fire({
        title: 'Delete Schedule?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete schedule
            Swal.fire('Deleted!', 'Schedule deleted successfully', 'success');
            loadSchedules();
        }
    });
}
</script>









