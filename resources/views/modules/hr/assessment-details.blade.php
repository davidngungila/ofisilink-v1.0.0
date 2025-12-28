@extends('layouts.app')

@section('title', 'Assessment Details - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bx bx-target-lock me-2"></i>Assessment Details
                            </h4>
                            <p class="mb-0 text-muted">Assessment ID: <strong>#{{ $assessment->id }}</strong></p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            @if($isAdmin || $isHR)
                            <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline-warning">
                                <i class="bx bx-edit me-1"></i>Edit Assessment
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Details -->
        <div class="col-lg-8">

            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>Assessment Status
                        </h5>
                        <span class="badge bg-light text-dark fs-6">{{ ucfirst(str_replace('_', ' ', $assessment->status)) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-user me-2"></i>Employee Name:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-building me-2"></i>Department:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->primaryDepartment->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-envelope me-2"></i>Email:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->email ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-calendar me-2"></i>Created:</strong><br>
                                <span class="ms-4">{{ $assessment->created_at->format('M j, Y g:i A') }}</span>
                            </p>
                            @if($assessment->hod_approved_at)
                            <p class="mb-2"><strong><i class="bx bx-check-circle me-2"></i>{{ $assessment->status === 'approved' ? 'Approved' : 'Rejected' }}:</strong><br>
                                <span class="ms-4">{{ $assessment->hod_approved_at->format('M j, Y g:i A') }}</span>
                                @if($assessment->hodApprover)
                                <br><small class="ms-4 text-muted">by {{ $assessment->hodApprover->name }}</small>
                                @endif
                            </p>
                            @endif
                            <p class="mb-2"><strong><i class="bx bx-percent me-2"></i>Contribution:</strong><br>
                                <span class="ms-4 badge bg-primary fs-6">{{ $assessment->contribution_percentage }}%</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Responsibility -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-target-lock me-2"></i>Main Responsibility</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $assessment->main_responsibility }}</h4>
                    @if($assessment->description)
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block mb-2">Description</small>
                        <p class="mb-0">{{ $assessment->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Performance Metrics ({{ $currentYear }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-primary">{{ $performanceData['total_reports'] }}</h3>
                                <small class="text-muted">Total Reports</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-success">{{ $performanceData['approved_reports'] }}</h3>
                                <small class="text-muted">Approved</small>
                                <br><small class="text-success">{{ $performanceData['approval_rate'] }}% Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-warning">{{ $performanceData['pending_reports'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-danger">{{ $performanceData['rejected_reports'] }}</h3>
                                <small class="text-muted">Rejected</small>
                                <br><small class="text-danger">{{ $performanceData['rejection_rate'] }}% Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities and Reports -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Activities & Progress Reports</h5>
                </div>
        <div class="card-body">
            @if($assessment->activities->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="bx bx-inbox" style="font-size: 3rem;"></i>
                <p class="mt-2">No activities found for this assessment.</p>
            </div>
            @else
            <div class="accordion" id="activitiesAccordion">
                @foreach($assessment->activities as $index => $activity)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $activity->id }}">
                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $activity->id }}">
                            <div class="d-flex justify-content-between w-100 me-3">
                                <div>
                                    <strong>{{ $activity->activity_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary">{{ ucfirst($activity->reporting_frequency) }}</span>
                                        <span class="badge bg-info">{{ $activity->contribution_percentage }}% Contribution</span>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        {{ $activity->progressReports->count() }} report(s)
                                    </small>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $activity->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#activitiesAccordion">
                        <div class="accordion-body">
                            @if($activity->description)
                            <p class="mb-3"><strong>Description:</strong> {{ $activity->description }}</p>
                            @endif
                            
                            @if($activity->progressReports->isEmpty())
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle"></i> No progress reports submitted yet for this activity.
                            </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Report Date</th>
                                            <th>Status</th>
                                            <th>Progress Text</th>
                                            <th>Approved By</th>
                                            <th>Approved At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activity->progressReports as $report)
                                        <tr>
                                            <td>{{ $report->report_date ? $report->report_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'pending_approval' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($report->progress_text, 100) }}</td>
                                            <td>{{ $report->hodApprover->name ?? 'N/A' }}</td>
                                            <td>{{ $report->hod_approved_at ? $report->hod_approved_at->format('M d, Y H:i') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-time me-2"></i>Timeline</h5>
                </div>
        <div class="card-body">
            @if(empty($timeline))
            <div class="text-center text-muted py-4">
                <p>No timeline events available.</p>
            </div>
            @else
            <div class="timeline">
                @foreach($timeline as $event)
                <div class="timeline-item mb-4">
                    <div class="d-flex">
                        <div class="timeline-marker me-3">
                            <div class="bg-{{ $event['color'] }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bx {{ $event['icon'] }} text-white"></i>
                            </div>
                        </div>
                        <div class="timeline-content flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0">{{ $event['event'] }}</h6>
                                <small class="text-muted">{{ $event['date']->format('M d, Y H:i') }}</small>
                            </div>
                            <p class="text-muted small mb-1"><strong>By:</strong> {{ $event['user'] }}</p>
                            @if($event['description'])
                            <p class="mb-0 small">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

        </div>

        <!-- Right Column - Quick Actions & Info -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            @if($isHOD || $isHR || $isAdmin)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($assessment->status === 'pending_hod')
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('assessments.approve', $assessment->id) }}" class="btn btn-success">
                            <i class="bx bx-check me-2"></i>Approve Assessment
                        </a>
                        <a href="{{ route('assessments.reject', $assessment->id) }}" class="btn btn-danger">
                            <i class="bx bx-x me-2"></i>Reject Assessment
                        </a>
                    </div>
                    @endif
                    @if($isAdmin || $isHR)
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-2"></i>Edit Assessment
                        </a>
                    </div>
                    @endif
                    @if($isAdmin)
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger btn-delete-assessment" 
                                data-id="{{ $assessment->id }}"
                                data-name="{{ $assessment->main_responsibility }}">
                            <i class="bx bx-trash me-2"></i>Delete Assessment
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Assessment Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Assessment Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Activities Count:</strong> {{ $assessment->activities->count() }}</p>
                    <p class="mb-2"><strong>Contribution:</strong> {{ $assessment->contribution_percentage }}%</p>
                    <p class="mb-0"><strong>Status:</strong> 
                        <span class="badge bg-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline-item {
    position: relative;
}
.timeline-marker {
    flex-shrink: 0;
}
.timeline-content {
    padding-top: 5px;
}
.modal {
    z-index: 9999 !important;
}
.modal-backdrop {
    z-index: 9998 !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Delete Assessment
    $('.btn-delete-assessment').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Delete assessment "${name}"? This will also delete all related activities and progress reports. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: `/assessments/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'Assessment deleted successfully', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("modules.hr.assessments") }}';
                            }, 1500);
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete assessment', 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

