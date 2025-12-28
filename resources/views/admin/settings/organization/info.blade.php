@extends('layouts.app')

@section('title', 'Organization Information')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .info-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }
    .upload-area:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
    }
    .upload-area.dragover {
        border-color: #007bff;
        background-color: #e7f1ff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-building me-2"></i>Organization Information
                            </h3>
                            <p class="mb-0 text-white-50">Manage company details, branding, and contact information</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="{{ route('admin.settings.organization') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="organizationInfoForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4 info-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-info-circle me-2 text-primary"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company Legal Name <span class="text-danger">*</span></label>
                                <input type="text" name="organization_settings[company_name]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['company_name']->value ?? '' }}" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trading Name</label>
                                <input type="text" name="organization_settings[trading_name]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['trading_name']->value ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Company Address</label>
                                <textarea name="organization_settings[address]" 
                                          class="form-control" 
                                          rows="3">{{ $organizationSettings['address']->value ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card border-0 shadow-sm mb-4 info-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-phone me-2 text-success"></i>Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="organization_settings[phone]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['phone']->value ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="organization_settings[email]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['email']->value ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Website</label>
                                <input type="url" name="organization_settings[website]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['website']->value ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tax ID</label>
                                <input type="text" name="organization_settings[tax_id]" 
                                       class="form-control form-control-lg" 
                                       value="{{ $organizationSettings['tax_id']->value ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Logo Upload -->
                <div class="card border-0 shadow-sm mb-4 info-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-image me-2 text-warning"></i>Company Logo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="upload-area" id="logoUploadArea">
                            <div id="logoPreview" class="mb-3">
                                <img src="{{ $orgSettings->company_logo ? asset('storage/' . $orgSettings->company_logo) : asset('assets/img/logo.png') }}" 
                                     alt="Company Logo" 
                                     class="img-fluid rounded shadow" 
                                     style="max-height: 200px; max-width: 100%;">
                            </div>
                            <input type="file" name="company_logo" id="companyLogo" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('companyLogo').click()">
                                <i class="bx bx-upload me-1"></i>Upload Logo
                            </button>
                            <p class="text-muted small mt-2 mb-0">Recommended: 300x300px, PNG/JPG</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm info-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-bolt me-2 text-info"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bx bx-save me-1"></i>Save Changes
                            </button>
                            <a href="{{ route('admin.settings.organization') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Logo preview
    $('#companyLogo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').html(`<img src="${e.target.result}" class="img-fluid rounded shadow" style="max-height: 200px; max-width: 100%;">`);
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#organizationInfoForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        
        Swal.fire({
            title: 'Saving...',
            html: 'Please wait while we save your changes',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        $.ajax({
            url: '{{ route("settings.update") }}',
            method: 'PUT',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Organization information updated successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                const errors = xhr.responseJSON?.errors || {};
                let errorMsg = xhr.responseJSON?.message || 'Error updating organization information';
                if(Object.keys(errors).length > 0) {
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMsg,
                    confirmButtonText: 'OK'
                });
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush










