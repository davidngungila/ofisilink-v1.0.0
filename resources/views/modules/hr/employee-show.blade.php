@extends('layouts.app')

@section('title', 'Employee Details - ' . $employee->name . ' - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Employee Details</h4>
        <p class="text-muted">Complete employee information and profile</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
        <div>
                            <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-user me-2"></i>Employee Complete Details - {{ $employee->name }}
                            </h5>
                            <p class="text-white-50 mb-0">Employee ID: {{ $employee->employee_id ?? 'N/A' }}</p>
        </div>
        <div>
                            <a href="{{ route('modules.hr.employees') }}" class="btn btn-light btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back to Employees
            </a>
                            @if($canEdit)
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-light btn-sm">
                                <i class="bx bx-edit me-1"></i>Edit Employee
                            </a>
                            @endif
                            <a href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" class="btn btn-light btn-sm" target="_blank">
                                <i class="bx bx-file-blank me-1"></i>Generate PDF
                            </a>
                        </div>
        </div>
    </div>
                <div class="card-body">
                    <!-- Status & Completion -->
                    <div class="alert alert-{{ $employee->is_active ? 'success' : 'secondary' }} d-flex justify-content-between align-items-center mb-4" role="alert">
                <div>
                    <i class="bx bx-{{ $employee->is_active ? 'check-circle' : 'x-circle' }} me-2"></i>
                    <strong>Status:</strong> {{ $employee->is_active ? 'Active' : 'Inactive' }}
                </div>
                    <div>
                        <strong>Profile Completion:</strong>
                        <div class="progress mt-1" style="width: 150px; height: 20px;">
                            <div class="progress-bar bg-{{ $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger') }}" 
                                 role="progressbar" 
                                 style="width: {{ $completionPercentage }}%"
                                 aria-valuenow="{{ $completionPercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($completionPercentage, 1) }}%
                            </div>
                        </div>
                    </div>
                    </div>

                    <!-- 1. Personal Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user me-2"></i>1. Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    @if($employee->photo)
                                    <img src="{{ asset('storage/photos/' . $employee->photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; margin: 0 auto;">
                                        <i class="bx bx-user" style="font-size: 60px; color: #ccc;"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-borderless">
                                        <tr><th width="200">Full Name:</th><td>{{ $employee->name }}</td></tr>
                                        <tr><th>Email:</th><td><a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a></td></tr>
                                        <tr><th>Phone:</th><td><a href="tel:{{ $employee->phone }}">{{ $employee->phone ?? 'N/A' }}</a></td></tr>
                                        <tr><th>Employee ID:</th><td><code>{{ $employee->employee_id ?? 'N/A' }}</code></td></tr>
                                        <tr><th>Department:</th><td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td></tr>
                                        <tr><th>Date of Birth:</th><td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</td></tr>
                                        <tr><th>Gender:</th><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                                        <tr><th>Marital Status:</th><td>{{ $employee->marital_status ?? 'N/A' }}</td></tr>
                                        <tr><th>Nationality:</th><td>{{ $employee->nationality ?? 'N/A' }}</td></tr>
                                        <tr><th>Address:</th><td>{{ $employee->address ?? 'N/A' }}</td></tr>
                                        <tr><th>Hire Date:</th><td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') : 'N/A' }}</td></tr>
                                        @if($employee->roles && $employee->roles->count() > 0)
                                        <tr><th>Roles:</th><td>
                                            @foreach($employee->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->display_name ?? $role->name }}</span>
                                            @endforeach
                                        </td></tr>
                                        @endif
                                    </table>
                </div>
            </div>
        </div>
    </div>

                    <!-- 2. Employment Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-briefcase me-2"></i>2. Employment Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Position:</th><td>{{ $employee->employee->position ?? 'N/A' }}</td></tr>
                                <tr><th>Employment Type:</th><td>
                                    <span class="badge bg-{{ $employee->employee->employment_type == 'permanent' ? 'success' : ($employee->employee->employment_type == 'contract' ? 'warning' : 'info') }}">
                                        {{ ucfirst($employee->employee->employment_type ?? 'N/A') }}
                                    </span>
                                </td></tr>
                                <tr><th>Hire Date:</th><td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') : 'N/A' }}</td></tr>
                                <tr><th>Salary:</th><td>{{ $employee->employee->salary ? 'TZS ' . number_format($employee->employee->salary, 2) : 'N/A' }}</td></tr>
                                <tr><th>Years of Service:</th><td>
                                    @if($employee->hire_date)
                                        {{ \Carbon\Carbon::parse($employee->hire_date)->diffInYears(now()) }} years
                        @else
                                        N/A
                        @endif
                                </td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Emergency Contact -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-phone-call me-2"></i>3. Emergency Contact</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Contact Name:</th><td>{{ $employee->employee->emergency_contact_name ?? 'N/A' }}</td></tr>
                                <tr><th>Contact Phone:</th><td>
                                    @if($employee->employee->emergency_contact_phone)
                                        <a href="tel:{{ $employee->employee->emergency_contact_phone }}">{{ $employee->employee->emergency_contact_phone }}</a>
                                    @else
                                        N/A
                    @endif
                                </td></tr>
                                <tr><th>Relationship:</th><td>{{ $employee->employee->emergency_contact_relationship ?? 'N/A' }}</td></tr>
                                <tr><th>Address:</th><td>{{ $employee->employee->emergency_contact_address ?? 'N/A' }}</td></tr>
                            </table>
                </div>
            </div>

                    <!-- 4. Family Information -->
                    @if($employee->family && $employee->family->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-group me-2"></i>4. Family Information</h5>
                </div>
                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Date of Birth</th>
                                            <th>Gender</th>
                                            <th>Occupation</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Dependent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->family as $member)
                                        <tr>
                                            <td><strong>{{ $member->name }}</strong></td>
                                            <td>{{ $member->relationship }}</td>
                                            <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $member->gender ?? 'N/A' }}</td>
                                            <td>{{ $member->occupation ?? 'N/A' }}</td>
                                            <td>{{ $member->phone ?? 'N/A' }}</td>
                                            <td>{{ $member->email ?? 'N/A' }}</td>
                                            <td><span class="badge bg-{{ $member->is_dependent ? 'success' : 'secondary' }}">{{ $member->is_dependent ? 'Yes' : 'No' }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-group me-2"></i>4. Family Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No family information recorded</p>
                        </div>
                    </div>
                    @endif

                    <!-- 5. Next of Kin -->
                    @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>5. Next of Kin</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>ID Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->nextOfKin as $kin)
                                        <tr>
                                            <td><strong>{{ $kin->name }}</strong></td>
                                            <td>{{ $kin->relationship }}</td>
                                            <td><a href="tel:{{ $kin->phone }}">{{ $kin->phone }}</a></td>
                                            <td>{{ $kin->email ?? 'N/A' }}</td>
                                            <td>{{ $kin->address }}</td>
                                            <td>{{ $kin->id_number ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>5. Next of Kin</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No next of kin information recorded</p>
                        </div>
                    </div>
                    @endif

                    <!-- 6. Referees -->
                    @if($employee->referees && $employee->referees->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-voice me-2"></i>6. Referees</h5>
                </div>
                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Organization</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Relationship</th>
                                            <th>Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->referees as $referee)
                                        <tr>
                                            <td><strong>{{ $referee->name }}</strong></td>
                                            <td>{{ $referee->position ?? 'N/A' }}</td>
                                            <td>{{ $referee->organization ?? 'N/A' }}</td>
                                            <td><a href="tel:{{ $referee->phone }}">{{ $referee->phone }}</a></td>
                                            <td>{{ $referee->email ?? 'N/A' }}</td>
                                            <td>{{ $referee->relationship ?? 'N/A' }}</td>
                                            <td>{{ $referee->address ?? 'N/A' }}</td>
                                        </tr>
                    @endforeach
                                    </tbody>
                                </table>
                </div>
            </div>
        </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-voice me-2"></i>6. Referees</h5>
                </div>
                <div class="card-body">
                            <p class="text-muted text-center py-3">No referees information recorded</p>
                        </div>
                            </div>
                    @endif

                    <!-- 7. Education Background -->
                    @if($employee->educations && $employee->educations->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-book me-2"></i>7. Education Background</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Institution</th>
                                            <th>Qualification</th>
                                            <th>Field of Study</th>
                                            <th>Start Year</th>
                                            <th>End Year</th>
                                            <th>Grade</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->educations as $education)
                                        <tr>
                                            <td><strong>{{ $education->institution_name }}</strong></td>
                                            <td>{{ $education->qualification }}</td>
                                            <td>{{ $education->field_of_study ?? 'N/A' }}</td>
                                            <td>{{ $education->start_year ?? 'N/A' }}</td>
                                            <td>{{ $education->end_year ?? 'N/A' }}</td>
                                            <td>{{ $education->grade ?? 'N/A' }}</td>
                                            <td>{{ $education->description ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-book me-2"></i>7. Education Background</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No education information recorded</p>
                </div>
            </div>
            @endif

                    <!-- 8. Banking Information -->
                    @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>8. Banking Information</h5>
                </div>
                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Account Number</th>
                                            <th>Account Name</th>
                                            <th>Branch Name</th>
                                            <th>SWIFT Code</th>
                                            <th>Primary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->bankAccounts as $account)
                                        <tr class="{{ $account->is_primary ? 'table-success' : '' }}">
                                            <td><strong>{{ $account->bank_name }}</strong></td>
                                            <td><code>{{ $account->account_number }}</code></td>
                                            <td>{{ $account->account_name ?? 'N/A' }}</td>
                                            <td>{{ $account->branch_name ?? 'N/A' }}</td>
                                            <td>{{ $account->swift_code ?? 'N/A' }}</td>
                                            <td>
                                                @if($account->is_primary)
                                                    <span class="badge bg-success">Primary</span>
                                                @else
                                                    <span class="badge bg-secondary">Secondary</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>8. Banking Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No banking information recorded</p>
                        </div>
                    </div>
                    @endif

                    <!-- 9. Salary Deductions -->
                    @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>9. Salary Deductions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Frequency</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->salaryDeductions as $deduction)
                                        <tr>
                                            <td><strong>{{ $deduction->deduction_type }}</strong></td>
                                            <td>{{ $deduction->description ?? 'N/A' }}</td>
                                            <td><strong class="text-danger">TZS {{ number_format($deduction->amount, 2) }}</strong></td>
                                            <td><span class="badge bg-info">{{ ucfirst($deduction->frequency) }}</span></td>
                                            <td>{{ \Carbon\Carbon::parse($deduction->start_date)->format('d M Y') }}</td>
                                            <td>{{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('d M Y') : 'Ongoing' }}</td>
                                            <td>
                                                <span class="badge {{ $deduction->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $deduction->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                            </td>
                                            <td>{{ $deduction->notes ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>9. Salary Deductions</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No salary deductions recorded</p>
                </div>
            </div>
            @endif

                    <!-- 10. Profile Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-circle me-2"></i>10. Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="200">Profile Photo:</th><td>
                                            @if($employee->photo)
                                                <img src="{{ asset('storage/photos/' . $employee->photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 100px;">
                                            @else
                                                <span class="text-muted">No photo uploaded</span>
                                            @endif
                                        </td></tr>
                                        <tr><th>Date of Birth:</th><td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</td></tr>
                                        <tr><th>Gender:</th><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                                        <tr><th>Marital Status:</th><td>{{ $employee->marital_status ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="200">Nationality:</th><td>{{ $employee->nationality ?? 'N/A' }}</td></tr>
                                        <tr><th>Address:</th><td>{{ $employee->address ?? 'N/A' }}</td></tr>
                                        <tr><th>Account Created:</th><td>{{ \Carbon\Carbon::parse($employee->created_at)->format('d M Y, h:i A') }}</td></tr>
                                        <tr><th>Last Updated:</th><td>{{ \Carbon\Carbon::parse($employee->updated_at)->format('d M Y, h:i A') }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 11. Documents -->
                    @if($employee->documents && $employee->documents->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>11. Documents</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Document Name</th>
                                            <th>Document Number</th>
                                            <th>Issue Date</th>
                                            <th>Expiry Date</th>
                                            <th>Issued By</th>
                                            <th>File Size</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->documents as $document)
                                        <tr>
                                            <td><strong>{{ $document->document_type }}</strong></td>
                                            <td>{{ $document->document_name }}</td>
                                            <td><code>{{ $document->document_number ?? 'N/A' }}</code></td>
                                            <td>{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @if($document->expiry_date)
                                                    @php
                                                        $expiryDate = \Carbon\Carbon::parse($document->expiry_date);
                                                        $isExpired = $expiryDate->isPast();
                                                        $isExpiringSoon = $expiryDate->diffInDays(now()) <= 30;
                                                    @endphp
                                                    <span class="{{ $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : '') }}">
                                                        {{ $expiryDate->format('d M Y') }}
                                                        @if($isExpired)
                                                            <span class="badge bg-danger">Expired</span>
                                                        @elseif($isExpiringSoon)
                                                            <span class="badge bg-warning">Expiring Soon</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $document->issued_by ?? 'N/A' }}</td>
                                            <td>
                                                @if($document->file_size)
                                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                                @else
                                                    N/A
                        @endif
                                            </td>
                                            <td>
                                                @if($document->file_path)
                                                    <a href="{{ asset('storage/documents/' . basename($document->file_path)) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download"></i> View
                                                    </a>
                                                @else
                                                    <span class="text-muted">No file</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>11. Documents</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No documents uploaded</p>
                        </div>
                    </div>
                    @endif

                    <!-- 12. Statutory Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-file me-2"></i>12. Statutory Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="200">TIN Number:</th><td><code>{{ $employee->employee->tin_number ?? 'N/A' }}</code></td></tr>
                                        <tr><th>NSSF Number:</th><td><code>{{ $employee->employee->nssf_number ?? 'N/A' }}</code></td></tr>
                                        <tr><th>NHIF Number:</th><td><code>{{ $employee->employee->nhif_number ?? 'N/A' }}</code></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="200">HESLB Number:</th><td><code>{{ $employee->employee->heslb_number ?? 'N/A' }}</code></td></tr>
                                        <tr><th>Has Student Loan:</th><td>
                                            <span class="badge bg-{{ $employee->employee->has_student_loan ? 'warning' : 'success' }}">
                                                {{ $employee->employee->has_student_loan ? 'Yes' : 'No' }}
                                            </span>
                                        </td></tr>
                                    </table>
                </div>
            </div>
        </div>
    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('modules.hr.employees') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back to Employees
                        </a>
                        <div>
                            <a href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bx bx-file-blank me-1"></i>Generate PDF
                            </a>
                            @if($canEdit)
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i>Edit Employee
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editEmployee(id) {
    window.location.href = '{{ route("employees.edit", ":id") }}'.replace(':id', id);
}

function uploadPhoto(id) {
    window.location.href = '{{ route("modules.hr.employees") }}?upload=' + id;
}
</script>
@endpush

@push('styles')
<style>
    .card-header {
        border-bottom: 2px solid #dee2e6;
    }
    .table th {
        font-weight: 600;
        color: #495057;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.35em 0.65em;
    }
    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
</style>
@endpush

