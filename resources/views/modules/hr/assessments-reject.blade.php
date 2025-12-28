@extends('layouts.app')

@section('title', 'Reject Assessment - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-x-circle me-2"></i>Reject Assessment</h4>
                            <p class="mb-0 text-muted">Assessment: <strong>{{ $assessment->main_responsibility }}</strong></p>
                        </div>
                        <a href="{{ route('assessments.show', $assessment->id) }}" class="btn btn-outline-secondary">
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
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0 text-white">Assessment Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Employee:</strong> {{ $assessment->employee->name }}</p>
                    <p><strong>Department:</strong> {{ $assessment->employee->primaryDepartment->name ?? 'N/A' }}</p>
                    @if($assessment->description)
                    <p><strong>Description:</strong> {{ $assessment->description }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Rejection Form</h5>
                </div>
                <div class="card-body">
                    <form id="reject-form">
                        @csrf
                        <input type="hidden" name="decision" value="reject">
                        <div class="mb-3">
                            <label class="form-label">Comments <span class="text-danger">*</span></label>
                            <textarea name="comments" class="form-control" rows="4" required placeholder="Provide reason for rejection..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bx bx-x me-2"></i>Reject Assessment
                            </button>
                            <a href="{{ route('assessments.show', $assessment->id) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
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
$('#reject-form').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Submitting...');
    
    $.ajax({
        type: 'POST',
        url: '{{ route("assessments.hod-approve", $assessment->id) }}',
        data: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
        success: function(response) {
            if (response.success) {
                window.location.href = '{{ route("assessments.show", $assessment->id) }}';
            } else {
                alert(response.message || 'Failed to reject');
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'An error occurred');
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
@endpush






