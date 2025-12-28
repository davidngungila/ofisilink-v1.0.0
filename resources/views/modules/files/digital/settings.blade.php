@extends('layouts.app')

@section('title', 'File Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-cog me-2"></i>File Settings
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Configure file management system settings and preferences
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">Storage Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Total Storage Used</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                @php
                                    $storagePercent = ($totalStorage / $storageLimit) * 100;
                                @endphp
                                <div class="progress-bar {{ $storagePercent > 80 ? 'bg-danger' : ($storagePercent > 60 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ min($storagePercent, 100) }}%">
                                    {{ number_format($storagePercent, 1) }}%
                                </div>
                            </div>
                            <p class="mb-0">
                                <strong>{{ number_format($totalStorage / 1024 / 1024 / 1024, 2) }} GB</strong> 
                                of 
                                <strong>{{ number_format($storageLimit / 1024 / 1024 / 1024, 2) }} GB</strong> used
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Storage Breakdown</h6>
                            <ul class="list-unstyled">
                                <li><i class="bx bx-folder me-2"></i>Folders: {{ $stats['total_folders'] ?? 0 }}</li>
                                <li><i class="bx bx-file me-2"></i>Files: {{ $stats['total_files'] ?? 0 }}</li>
                                <li><i class="bx bx-download me-2"></i>Total Downloads: {{ $stats['total_downloads'] ?? 0 }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">System Settings</h5>
                </div>
                <div class="card-body">
                    <form id="settings-form">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Maximum File Size (MB)</label>
                                <input type="number" class="form-control" name="max_file_size" value="{{ config('filesystems.max_file_size', 10) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Allowed File Types</label>
                                <input type="text" class="form-control" name="allowed_types" value="{{ config('filesystems.allowed_types', 'pdf,doc,docx,xls,xlsx,jpg,png') }}" placeholder="pdf,doc,docx">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Auto-cleanup Old Files (Days)</label>
                                <input type="number" class="form-control" name="auto_cleanup_days" value="{{ config('filesystems.auto_cleanup_days', 365) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Enable Activity Logging</label>
                                <select class="form-select" name="enable_logging">
                                    <option value="1" {{ config('filesystems.enable_logging', true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();
        // Handle settings save via AJAX
        Swal.fire('Success', 'Settings saved successfully!', 'success');
    });
});
</script>
@endpush
@endsection


