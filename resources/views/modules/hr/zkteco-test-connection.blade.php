@extends('layouts.app')

@section('title', 'Test Device Connection')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-wifi"></i> Test Device Connection
                </h4>
                <p class="text-muted">Test connection to ZKTeco biometric device</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-wifi me-2"></i>Device Connection Test</h5>
                </div>
                <div class="card-body">
                    <form id="testConnectionForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Device IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                       value="{{ old('ip_address', '192.168.100.108') }}" 
                                       placeholder="192.168.100.108" required>
                                <small class="text-muted">Enter the IP address of your ZKTeco device</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Port</label>
                                <input type="number" class="form-control" id="port" name="port" 
                                       value="{{ old('port', 4370) }}" 
                                       placeholder="4370" min="1" max="65535">
                                <small class="text-muted">Default: 4370</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Comm Key</label>
                                <input type="number" class="form-control" id="comm_key" name="comm_key" 
                                       value="{{ old('comm_key', 0) }}" 
                                       placeholder="0" min="0" max="65535">
                                <small class="text-muted">Default: 0</small>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="testBtn">
                                <i class="bx bx-wifi me-2"></i>Test Connection
                            </button>
                        </div>
                    </form>

                    <div id="testResult" class="mt-4" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('testConnectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('testBtn');
    const resultDiv = document.getElementById('testResult');
    const originalBtnText = btn.innerHTML;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
    resultDiv.style.display = 'none';
    
    const formData = {
        ip_address: document.getElementById('ip_address').value,
        port: parseInt(document.getElementById('port').value) || 4370,
        comm_key: parseInt(document.getElementById('comm_key').value) || 0
    };
    
    fetch('{{ route("zkteco.test.api") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.style.display = 'block';
        
        if (data.success) {
            let html = '<div class="alert alert-success">';
            html += '<h5><i class="bx bx-check-circle me-2"></i>Connection Successful!</h5>';
            html += '<hr>';
            
            if (data.device_info) {
                html += '<h6>Device Information:</h6>';
                html += '<ul class="mb-0">';
                if (data.device_info.device_name) {
                    html += '<li><strong>Device Name:</strong> ' + data.device_info.device_name + '</li>';
                }
                if (data.device_info.serial_number) {
                    html += '<li><strong>Serial Number:</strong> ' + data.device_info.serial_number + '</li>';
                }
                if (data.device_info.version) {
                    html += '<li><strong>Firmware Version:</strong> ' + data.device_info.version + '</li>';
                }
                html += '</ul>';
            }
            
            if (data.users_count !== undefined) {
                html += '<p class="mb-0 mt-2"><strong>Users on Device:</strong> ' + data.users_count + '</p>';
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' +
                '<h5><i class="bx bx-x-circle me-2"></i>Connection Failed</h5>' +
                '<p class="mb-0">' + (data.message || 'Unknown error') + '</p>' +
                '</div>';
        }
    })
    .catch(error => {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger">' +
            '<h5><i class="bx bx-x-circle me-2"></i>Error</h5>' +
            '<p class="mb-0">' + error.message + '</p>' +
            '</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
});
</script>
@endpush

