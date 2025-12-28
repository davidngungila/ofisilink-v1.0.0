@extends('layouts.app')

@section('title', 'Register User to Device')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-user-plus"></i> Register User to Device
                </h4>
                <p class="text-muted">Register system users to ZKTeco biometric device</p>
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
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-user-plus me-2"></i>Register User to Device</h5>
                </div>
                <div class="card-body">
                    <form id="registerUserForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" 
                                                data-enroll-id="{{ $emp->enroll_id ?? '' }}"
                                                data-name="{{ $emp->name }}">
                                            {{ $emp->name }} 
                                            @if($emp->employee && $emp->employee->employee_id)
                                                ({{ $emp->employee->employee_id }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Enroll ID</label>
                                <input type="text" class="form-control" id="enroll_id_display" readonly>
                                <small class="text-muted">Auto-generated from Employee ID</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Device IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                       value="{{ old('ip_address', '192.168.100.108') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <input type="number" class="form-control" id="port" name="port" 
                                       value="{{ old('port', 4370) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Comm Key</label>
                                <input type="number" class="form-control" id="comm_key" name="comm_key" 
                                       value="{{ old('comm_key', 0) }}">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="registerBtn">
                                <i class="bx bx-user-plus me-2"></i>Register User to Device
                            </button>
                        </div>
                    </form>

                    <div id="registerResult" class="mt-4" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Update enroll ID when user is selected
document.getElementById('user_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const enrollId = selectedOption.getAttribute('data-enroll-id');
    document.getElementById('enroll_id_display').value = enrollId || 'Will be generated';
});

document.getElementById('registerUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('user_id').value;
    if (!userId) {
        Swal.fire('Error', 'Please select an employee', 'error');
        return;
    }
    
    const btn = document.getElementById('registerBtn');
    const resultDiv = document.getElementById('registerResult');
    const originalBtnText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registering...';
    resultDiv.style.display = 'none';
    
    const formData = {
        user_id: userId,
        ip_address: document.getElementById('ip_address').value,
        port: parseInt(document.getElementById('port').value) || 4370,
        comm_key: parseInt(document.getElementById('comm_key').value) || 0
    };
    
    fetch('{{ route("zkteco.register.api") }}', {
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
            resultDiv.innerHTML = '<div class="alert alert-success">' +
                '<h5><i class="bx bx-check-circle me-2"></i>Registration Successful!</h5>' +
                '<p class="mb-0">User has been successfully registered to the device.</p>' +
                '</div>';
            
            Swal.fire('Success', 'User registered successfully!', 'success');
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' +
                '<h5><i class="bx bx-x-circle me-2"></i>Registration Failed</h5>' +
                '<p class="mb-0">' + (data.message || 'Unknown error') + '</p>' +
                '</div>';
            
            Swal.fire('Error', data.message || 'Registration failed', 'error');
        }
    })
    .catch(error => {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger">' +
            '<h5><i class="bx bx-x-circle me-2"></i>Error</h5>' +
            '<p class="mb-0">' + error.message + '</p>' +
            '</div>';
        
        Swal.fire('Error', error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
});
</script>
@endpush

