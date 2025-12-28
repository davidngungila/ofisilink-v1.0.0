@extends('layouts.app')

@section('title', 'Submit Progress Report - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-file me-2"></i>Submit Progress Report</h4>
                            <p class="mb-0 text-muted">Activity: <strong>{{ $activity->activity_name }}</strong></p>
                        </div>
                        <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Progress Report Form</h5>
                </div>
                <div class="card-body">
                    <form id="progress-form">
                        @csrf
                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Activity</label>
                            <input type="text" class="form-control" value="{{ $activity->activity_name }}" readonly>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Report Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="report_date" required>
                                <small class="text-muted">Select the date for this progress report</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frequency</label>
                                <input type="text" class="form-control" value="{{ ucfirst($activity->reporting_frequency) }}" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Progress <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="progress_text" rows="6" required placeholder="Describe your progress for this period..."></textarea>
                        </div>
                        
                        <div class="alert alert-danger d-none" id="progress-error"></div>
                        <div class="alert alert-success d-none" id="progress-success"></div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bx bx-check me-2"></i>Submit Report
                            </button>
                            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    $('input[name="report_date"]').val(today);
    
    $('#progress-form').on('submit', function(e) {
        e.preventDefault();
        const err = $('#progress-error');
        const ok = $('#progress-success');
        err.addClass('d-none');
        ok.addClass('d-none');
        
        const date = $('input[name="report_date"]').val();
        const text = $('textarea[name="progress_text"]').val().trim();
        
        if (!date || !text) {
            err.text('Report date and progress text are required.').removeClass('d-none');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: '{{ route("assessments.progress-report", $activity->id) }}',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ report_date: date, progress_text: text }),
            success: function(response) {
                if (response.success) {
                    ok.text(response.message || 'Progress report submitted successfully!').removeClass('d-none');
                    setTimeout(() => {
                        window.location.href = '{{ route("modules.hr.assessments") }}';
                    }, 1500);
                } else {
                    err.text(response.message || 'Failed to submit').removeClass('d-none');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                err.text(xhr.responseJSON?.message || 'Failed to submit report').removeClass('d-none');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush






