@extends('layouts.app')

@section('title', 'Review Employee Registration - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Review Employee Registration</h4>
        <p class="text-muted">Review all details before finalizing registration</p>
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
                                <i class="bx bx-check-circle me-2"></i>Employee Registration Review
                            </h5>
                            <p class="text-white-50 mb-0">Review all information before finalizing</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" class="btn btn-light btn-sm" target="_blank">
                                <i class="bx bx-file-blank me-1"></i> Generate PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Personal Information -->
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
                                        <tr><th>Email:</th><td>{{ $employee->email }}</td></tr>
                                        <tr><th>Phone:</th><td>{{ $employee->phone ?? 'N/A' }}</td></tr>
                                        <tr><th>Employee ID:</th><td>{{ $employee->employee_id ?? 'N/A' }}</td></tr>
                                        <tr><th>Department:</th><td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td></tr>
                                        <tr><th>Date of Birth:</th><td>{{ $employee->date_of_birth ? $employee->date_of_birth->format('d M Y') : 'N/A' }}</td></tr>
                                        <tr><th>Gender:</th><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                                        <tr><th>Marital Status:</th><td>{{ $employee->marital_status ?? 'N/A' }}</td></tr>
                                        <tr><th>Nationality:</th><td>{{ $employee->nationality ?? 'N/A' }}</td></tr>
                                        <tr><th>Address:</th><td>{{ $employee->address ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-briefcase me-2"></i>2. Employment Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Position:</th><td>{{ $employee->employee->position ?? 'N/A' }}</td></tr>
                                <tr><th>Employment Type:</th><td>{{ ucfirst($employee->employee->employment_type ?? 'N/A') }}</td></tr>
                                <tr><th>Hire Date:</th><td>{{ $employee->hire_date ? $employee->hire_date->format('d M Y') : 'N/A' }}</td></tr>
                                <tr><th>Salary:</th><td>{{ $employee->employee->salary ? 'TZS ' . number_format($employee->employee->salary, 2) : 'N/A' }}</td></tr>
                                <tr><th>Roles:</th><td>
                                    @if($employee->roles->count() > 0)
                                        @foreach($employee->roles as $role)
                                            <span class="badge bg-primary">{{ $role->display_name }}</span>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-phone-call me-2"></i>3. Emergency Contact</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Contact Name:</th><td>{{ $employee->employee->emergency_contact_name ?? 'N/A' }}</td></tr>
                                <tr><th>Contact Phone:</th><td>{{ $employee->employee->emergency_contact_phone ?? 'N/A' }}</td></tr>
                                <tr><th>Relationship:</th><td>{{ $employee->employee->emergency_contact_relationship ?? 'N/A' }}</td></tr>
                                <tr><th>Address:</th><td>{{ $employee->employee->emergency_contact_address ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Family Information -->
                    @if($employee->family && $employee->family->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-group me-2"></i>4. Family Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Date of Birth</th>
                                            <th>Gender</th>
                                            <th>Occupation</th>
                                            <th>Phone</th>
                                            <th>Dependent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->family as $member)
                                        <tr>
                                            <td>{{ $member->name }}</td>
                                            <td>{{ $member->relationship }}</td>
                                            <td>{{ $member->date_of_birth ? $member->date_of_birth->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $member->gender ?? 'N/A' }}</td>
                                            <td>{{ $member->occupation ?? 'N/A' }}</td>
                                            <td>{{ $member->phone ?? 'N/A' }}</td>
                                            <td>{{ $member->is_dependent ? 'Yes' : 'No' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Next of Kin -->
                    @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>5. Next of Kin</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
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
                                            <td>{{ $kin->name }}</td>
                                            <td>{{ $kin->relationship }}</td>
                                            <td>{{ $kin->phone }}</td>
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
                    @endif

                    <!-- Referees -->
                    @if($employee->referees && $employee->referees->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-voice me-2"></i>6. Referees</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Organization</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Relationship</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->referees as $referee)
                                        <tr>
                                            <td>{{ $referee->name }}</td>
                                            <td>{{ $referee->position ?? 'N/A' }}</td>
                                            <td>{{ $referee->organization ?? 'N/A' }}</td>
                                            <td>{{ $referee->phone }}</td>
                                            <td>{{ $referee->email ?? 'N/A' }}</td>
                                            <td>{{ $referee->relationship ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Education -->
                    @if($employee->educations && $employee->educations->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-book me-2"></i>7. Education Background</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Institution</th>
                                            <th>Qualification</th>
                                            <th>Field of Study</th>
                                            <th>Start Year</th>
                                            <th>End Year</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->educations as $education)
                                        <tr>
                                            <td>{{ $education->institution_name }}</td>
                                            <td>{{ $education->qualification }}</td>
                                            <td>{{ $education->field_of_study ?? 'N/A' }}</td>
                                            <td>{{ $education->start_year ?? 'N/A' }}</td>
                                            <td>{{ $education->end_year ?? 'N/A' }}</td>
                                            <td>{{ $education->grade ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Banking Information -->
                    @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>8. Banking Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Account Number</th>
                                            <th>Account Name</th>
                                            <th>Branch</th>
                                            <th>SWIFT Code</th>
                                            <th>Primary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->bankAccounts as $account)
                                        <tr>
                                            <td>{{ $account->bank_name }}</td>
                                            <td>{{ $account->account_number }}</td>
                                            <td>{{ $account->account_name ?? 'N/A' }}</td>
                                            <td>{{ $account->branch_name ?? 'N/A' }}</td>
                                            <td>{{ $account->swift_code ?? 'N/A' }}</td>
                                            <td>{{ $account->is_primary ? 'Yes' : 'No' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Deductions -->
                    @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>9. Salary Deductions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Frequency</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->salaryDeductions as $deduction)
                                        <tr>
                                            <td>{{ $deduction->deduction_type }}</td>
                                            <td>{{ $deduction->description ?? 'N/A' }}</td>
                                            <td>TZS {{ number_format($deduction->amount, 2) }}</td>
                                            <td>{{ ucfirst($deduction->frequency) }}</td>
                                            <td>{{ $deduction->start_date->format('d M Y') }}</td>
                                            <td>{{ $deduction->end_date ? $deduction->end_date->format('d M Y') : 'Ongoing' }}</td>
                                            <td><span class="badge {{ $deduction->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $deduction->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Statutory Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-file me-2"></i>10. Statutory Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">TIN Number:</th><td>{{ $employee->employee->tin_number ?? 'N/A' }}</td></tr>
                                <tr><th>NSSF Number:</th><td>{{ $employee->employee->nssf_number ?? 'N/A' }}</td></tr>
                                <tr><th>NHIF Number:</th><td>{{ $employee->employee->nhif_number ?? 'N/A' }}</td></tr>
                                <tr><th>HESLB Number:</th><td>{{ $employee->employee->heslb_number ?? 'N/A' }}</td></tr>
                                <tr><th>Has Student Loan:</th><td>{{ $employee->employee->has_student_loan ? 'Yes' : 'No' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Documents -->
                    @if($employee->documents && $employee->documents->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>11. Documents</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Document Name</th>
                                            <th>Document Number</th>
                                            <th>Issue Date</th>
                                            <th>Expiry Date</th>
                                            <th>Issued By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->documents as $document)
                                        <tr>
                                            <td>{{ $document->document_type }}</td>
                                            <td>{{ $document->document_name }}</td>
                                            <td>{{ $document->document_number ?? 'N/A' }}</td>
                                            <td>{{ $document->issue_date ? $document->issue_date->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $document->expiry_date ? $document->expiry_date->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $document->issued_by ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ asset('storage/documents/' . basename($document->file_path)) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-download"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('modules.hr.employees.register') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Edit
                        </a>
                        <div>
                            <a href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bx bx-file-blank me-1"></i> Generate PDF
                            </a>
                            <button type="button" class="btn btn-success" onclick="finalizeRegistration()">
                                <i class="bx bx-check me-1"></i> Finalize Registration & Send SMS
                            </button>
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
function finalizeRegistration() {
    if (!confirm('Are you sure you want to finalize this registration? This will:\n1. Activate the employee account\n2. Send welcome SMS to employee with username and password\n3. Send congratulations SMS to employee\n4. Send notification SMS to HOD (Head of Department)\n5. Send notification SMS to CEO\n6. Send notification SMS to HR Officers\n\nThis action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("modules.hr.employees.finalize", $employee->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Build detailed SMS information popup
                let smsDetails = '<div class="text-start">';
                smsDetails += '<h5 class="mb-3"><i class="bx bx-check-circle text-success me-2"></i>Registration Finalized Successfully!</h5>';
                
                if (response.sms_results) {
                    smsDetails += '<div class="mb-3"><strong>SMS Notifications Sent:</strong></div>';
                    
                    // Welcome SMS
                    if (response.sms_results.welcome) {
                        const welcome = response.sms_results.welcome;
                        if (welcome.sent && welcome.phone) {
                            smsDetails += '<div class="alert alert-success mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-check me-1"></i>Welcome SMS:</strong><br>';
                            smsDetails += '<small><strong>To:</strong> ' + welcome.phone + '</small><br>';
                            smsDetails += '<small><strong>Message:</strong><br><em>' + (welcome.message ? welcome.message.substring(0, 100) + '...' : 'Sent successfully') + '</em></small>';
                            smsDetails += '</div>';
                        } else {
                            smsDetails += '<div class="alert alert-warning mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-x me-1"></i>Welcome SMS:</strong> Not sent';
                            if (welcome.phone) {
                                smsDetails += '<br><small>Phone: ' + welcome.phone + '</small>';
                            } else {
                                smsDetails += '<br><small>No phone number available</small>';
                            }
                            smsDetails += '</div>';
                        }
                    }
                    
                    // Congratulations SMS
                    if (response.sms_results.congratulations) {
                        const congrats = response.sms_results.congratulations;
                        if (congrats.sent && congrats.phone) {
                            smsDetails += '<div class="alert alert-success mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-check me-1"></i>Congratulations SMS:</strong><br>';
                            smsDetails += '<small><strong>To:</strong> ' + congrats.phone + '</small><br>';
                            smsDetails += '<small><strong>Message:</strong><br><em>' + (congrats.message ? congrats.message.substring(0, 100) + '...' : 'Sent successfully') + '</em></small>';
                            smsDetails += '</div>';
                        } else {
                            smsDetails += '<div class="alert alert-warning mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-x me-1"></i>Congratulations SMS:</strong> Not sent';
                            if (congrats.phone) {
                                smsDetails += '<br><small>Phone: ' + congrats.phone + '</small>';
                            } else {
                                smsDetails += '<br><small>No phone number available</small>';
                            }
                            smsDetails += '</div>';
                        }
                    }
                    
                    // HOD Notification SMS
                    if (response.sms_results.hod) {
                        const hod = response.sms_results.hod;
                        if (hod.sent && hod.phone) {
                            smsDetails += '<div class="alert alert-success mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-check me-1"></i>HOD Notification SMS:</strong><br>';
                            smsDetails += '<small><strong>To:</strong> ' + (hod.recipient || 'HOD') + ' (' + hod.phone + ')</small><br>';
                            smsDetails += '<small><strong>Message:</strong><br><em>' + (hod.message ? hod.message.substring(0, 100) + '...' : 'Sent successfully') + '</em></small>';
                            smsDetails += '</div>';
                        } else {
                            smsDetails += '<div class="alert alert-warning mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-x me-1"></i>HOD Notification SMS:</strong> Not sent';
                            if (hod.message) {
                                smsDetails += '<br><small>' + hod.message + '</small>';
                            }
                            smsDetails += '</div>';
                        }
                    }
                    
                    // CEO Notification SMS
                    if (response.sms_results.ceo) {
                        const ceo = response.sms_results.ceo;
                        if (ceo.sent && ceo.phone) {
                            smsDetails += '<div class="alert alert-success mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-check me-1"></i>CEO Notification SMS:</strong><br>';
                            const recipients = ceo.recipients && ceo.recipients.length > 0 ? ceo.recipients.join(', ') : (ceo.recipient || 'CEO');
                            smsDetails += '<small><strong>To:</strong> ' + recipients + (ceo.phone ? ' (' + ceo.phone + ')' : '') + '</small><br>';
                            smsDetails += '<small><strong>Message:</strong><br><em>' + (ceo.message ? ceo.message.substring(0, 100) + '...' : 'Sent successfully') + '</em></small>';
                            smsDetails += '</div>';
                        } else {
                            smsDetails += '<div class="alert alert-warning mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-x me-1"></i>CEO Notification SMS:</strong> Not sent';
                            if (ceo.message) {
                                smsDetails += '<br><small>' + ceo.message + '</small>';
                            }
                            smsDetails += '</div>';
                        }
                    }
                    
                    // HR Notification SMS
                    if (response.sms_results.hr) {
                        const hr = response.sms_results.hr;
                        if (hr.sent && hr.phone) {
                            smsDetails += '<div class="alert alert-success mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-check me-1"></i>HR Notification SMS:</strong><br>';
                            const recipients = hr.recipients && hr.recipients.length > 0 ? hr.recipients.join(', ') : (hr.recipient || 'HR Officer');
                            smsDetails += '<small><strong>To:</strong> ' + recipients + (hr.phone ? ' (' + hr.phone + ')' : '') + '</small><br>';
                            smsDetails += '<small><strong>Message:</strong><br><em>' + (hr.message ? hr.message.substring(0, 100) + '...' : 'Sent successfully') + '</em></small>';
                            smsDetails += '</div>';
                        } else {
                            smsDetails += '<div class="alert alert-warning mb-2">';
                            smsDetails += '<strong><i class="bx bx-message-x me-1"></i>HR Notification SMS:</strong> Not sent';
                            if (hr.message) {
                                smsDetails += '<br><small>' + hr.message + '</small>';
                            }
                            smsDetails += '</div>';
                        }
                    }
                }
                
                smsDetails += '<div class="mt-3"><button class="btn btn-primary w-100" onclick="window.location.href=\'{{ route("modules.hr.employees") }}\'">Go to Employees List</button></div>';
                smsDetails += '</div>';
                
                // Show SweetAlert or custom modal
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Finalized!',
                        html: smsDetails,
                        width: '600px',
                        confirmButtonText: 'Go to Employees List',
                        confirmButtonColor: '#940000',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("modules.hr.employees") }}';
                        }
                    });
                } else {
                    // Fallback to custom modal
                    const modal = `
                        <div class="modal fade show" id="smsDetailsModal" style="display: block; z-index: 9999;" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title"><i class="bx bx-check-circle me-2"></i>Registration Finalized Successfully!</h5>
                                        <button type="button" class="btn-close btn-close-white" onclick="$('#smsDetailsModal').remove()"></button>
                                    </div>
                                    <div class="modal-body">
                                        ${smsDetails}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-backdrop fade show"></div>
                    `;
                    $('body').append(modal);
                }
            } else {
                alert('❌ ' + (response.message || 'Failed to finalize registration'));
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to finalize registration';
            alert('❌ ' + errorMsg);
        }
    });
}
</script>
@endpush

