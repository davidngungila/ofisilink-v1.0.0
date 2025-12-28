@extends('layouts.app')

@section('title', 'Work Schedules')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-time-five"></i> Work Schedules
                </h4>
                <p class="text-muted">Configure work schedules, shifts, and time policies</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <button type="button" class="btn btn-primary" onclick="openScheduleModal()">
                    <i class="bx bx-plus me-1"></i> Add Schedule
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .border-left-info { border-left: 4px solid #0dcaf0; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Schedules</h6>
                            <h3 class="mb-0" id="statTotalSchedules">{{ $stats['total_schedules'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-time-five fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Schedules</h6>
                            <h3 class="mb-0 text-success" id="statActiveSchedules">{{ $stats['active_schedules'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Schedules List
                    </h6>
                </div>
                <div class="card-body">
                    @include('modules.hr.attendance-settings.partials.schedules')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
@include('modules.hr.attendance-settings.modals.schedule-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const schedulesData = @json($schedules ?? []);

document.addEventListener('DOMContentLoaded', function() {
    loadSchedules();
});

function loadSchedules() {
    const tbody = document.getElementById('schedulesList');
    if (!tbody) return;

    if (!schedulesData || schedulesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No schedules found</p></td></tr>';
        return;
    }
    
    tbody.innerHTML = schedulesData.map(schedule => `
        <tr>
            <td><strong>${schedule.name || 'N/A'}</strong></td>
            <td><code>${schedule.code || 'N/A'}</code></td>
            <td>${schedule.start_time || 'N/A'} - ${schedule.end_time || 'N/A'}</td>
            <td>${schedule.work_hours || 0} hrs</td>
            <td>${schedule.location?.name || 'All'}</td>
            <td>${schedule.department?.name || 'All'}</td>
            <td>${schedule.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editSchedule(${schedule.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(${schedule.id}, '${schedule.name || ''}')" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openScheduleModal(scheduleId = null) {
    // Open schedule modal logic
    console.log('Open schedule modal', scheduleId);
}

function editSchedule(id) {
    openScheduleModal(id);
}

function deleteSchedule(id, name) {
    Swal.fire({
        title: 'Delete Schedule',
        html: 'Are you sure you want to delete <strong>' + name + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/attendance-settings/schedules/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadSchedules();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete schedule', 'error');
            });
        }
    });
}
</script>
@endpush









