@extends('layouts.app')

@section('title', 'Create User - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Create New User</h4>
        <p class="text-muted">Add a new user to the system</p>
      </div>
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">User Information</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
          @csrf
          
          <!-- Basic Information -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                     value="{{ old('name') }}" required>
              @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                     value="{{ old('email') }}" required>
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                     required>
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
          </div>

          <!-- Employee Information -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Employee ID</label>
              <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" 
                     value="{{ old('employee_id') }}">
              @error('employee_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                     value="{{ old('phone') }}">
              @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Hire Date</label>
              <input type="date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" 
                     value="{{ old('hire_date') }}">
              @error('hire_date')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Primary Department <span class="text-danger">*</span></label>
              <select name="primary_department_id" class="form-select @error('primary_department_id') is-invalid @enderror" required>
                <option value="">Select Department</option>
                @foreach(\App\Models\Department::where('is_active', true)->get() as $department)
                <option value="{{ $department->id }}" {{ old('primary_department_id') == $department->id ? 'selected' : '' }}>
                  {{ $department->name }}
                </option>
                @endforeach
              </select>
              @error('primary_department_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Roles Assignment -->
          <div class="mb-3">
            <label class="form-label">Assign Roles <span class="text-danger">*</span></label>
            <div class="row">
              @foreach(\App\Models\Role::where('is_active', true)->get() as $role)
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" 
                         id="role_{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                  <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->display_name }}
                  </label>
                  <small class="text-muted d-block">{{ $role->description }}</small>
                </div>
              </div>
              @endforeach
            </div>
            @error('roles')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          <!-- Additional Departments -->
          <div class="mb-3">
            <label class="form-label">Additional Departments</label>
            <div class="row">
              @foreach(\App\Models\Department::where('is_active', true)->get() as $department)
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="departments[]" value="{{ $department->id }}" 
                         id="dept_{{ $department->id }}" {{ in_array($department->id, old('departments', [])) ? 'checked' : '' }}>
                  <label class="form-check-label" for="dept_{{ $department->id }}">
                    {{ $department->name }}
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>

          <!-- Status -->
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                     {{ old('is_active', true) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_active">
                Active User
              </label>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
              <i class="bx bx-x"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save"></i> Create User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        const roles = $('input[name="roles[]"]:checked').length;
        if (roles === 0) {
            e.preventDefault();
            alert('Please select at least one role for the user.');
            return false;
        }
    });

    // Password strength indicator
    $('input[name="password"]').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        // You can add visual feedback here
    });

    function getPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }
});
</script>
@endpush