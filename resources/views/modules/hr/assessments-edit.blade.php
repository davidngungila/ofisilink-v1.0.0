@extends('layouts.app')

@section('title', 'Edit Assessment - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-edit me-2"></i>Edit Assessment</h4>
                            <p class="mb-0 text-muted">Assessment ID: <strong>#{{ $assessment->id }}</strong></p>
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
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Assessment Information</h5>
                </div>
                <div class="card-body">
                    <form id="edit-assessment-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Main Responsibility <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="main_responsibility" value="{{ $assessment->main_responsibility }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $assessment->description }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contribution Percentage <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="contribution_percentage" value="{{ $assessment->contribution_percentage }}" min="0" max="100" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="pending_hod" {{ $assessment->status === 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                                    <option value="approved" {{ $assessment->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $assessment->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle"></i> Editing this assessment will update all related data.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bx bx-save me-2"></i>Update Assessment
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
$('#edit-assessment-form').on('submit', function(e) {
    e.preventDefault();
    const id = $('input[name="assessment_id"]').val();
    const formData = {
        main_responsibility: $('input[name="main_responsibility"]').val(),
        description: $('textarea[name="description"]').val(),
        contribution_percentage: parseFloat($('input[name="contribution_percentage"]').val()),
        status: $('select[name="status"]').val()
    };
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Updating...');
    
    $.ajax({
        type: 'PUT',
        url: '/assessments/' + id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                window.location.href = '/assessments/' + id;
            } else {
                alert(response.message || 'Failed to update');
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






