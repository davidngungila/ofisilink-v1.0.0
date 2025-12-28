@extends('layouts.app')

@section('title', 'Register New Employee - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Register New Employee</h4>
        <p class="text-muted">Complete employee registration with step-by-step wizard</p>
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
                                <i class="bx bx-user-plus me-2"></i>Employee Registration Wizard
                            </h5>
                            <p class="text-white-50 mb-0">Fill in all required information step by step</p>
                        </div>
                        <div>
                            <span class="badge bg-light text-primary" id="progressBadge">0% Complete</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Registration Progress</span>
                            <span class="text-muted" id="progressText">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 id="progressBar" 
                                 style="width: 0%;" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="progressPercentage">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stage Indicators -->
                    <div class="mb-4">
                        <div class="d-flex flex-wrap gap-2" id="stageIndicators">
                            <span class="badge bg-primary stage-badge" data-stage="personal">
                                <i class="bx bx-check me-1"></i>1. Personal Info
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="employment">
                                <i class="bx bx-circle me-1"></i>2. Employment
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="emergency">
                                <i class="bx bx-circle me-1"></i>3. Emergency Contact
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="family">
                                <i class="bx bx-circle me-1"></i>4. Family
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="next-of-kin">
                                <i class="bx bx-circle me-1"></i>5. Next of Kin
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="referees">
                                <i class="bx bx-circle me-1"></i>6. Referees
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="education">
                                <i class="bx bx-circle me-1"></i>7. Education
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="banking">
                                <i class="bx bx-circle me-1"></i>8. Banking
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="deductions">
                                <i class="bx bx-circle me-1"></i>9. Deductions
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="profile">
                                <i class="bx bx-circle me-1"></i>10. Profile
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="documents">
                                <i class="bx bx-circle me-1"></i>11. Documents
                            </span>
                            <span class="badge bg-secondary stage-badge" data-stage="statutory">
                                <i class="bx bx-circle me-1"></i>12. Statutory Info
                            </span>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <form id="employeeRegistrationForm">
                        @csrf
                        <input type="hidden" name="user_id" id="userId">
                        <input type="hidden" name="stage" id="currentStage" value="personal">
                        <input type="hidden" name="save_as_draft" value="1">

                        <!-- Step 1: Personal Information -->
                        <div class="registration-step" id="step-personal" data-stage="personal">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user me-2"></i>Step 1: Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control" placeholder="255XXXXXXXXX">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Primary Department <span class="text-danger">*</span></label>
                                            <select name="primary_department_id" class="form-select" required>
                                                <option value="">Select Department</option>
                                                @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hire Date</label>
                                            <input type="date" name="hire_date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Employment Details -->
                        <div class="registration-step d-none" id="step-employment" data-stage="employment">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-briefcase me-2"></i>Step 2: Employment Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Position/Job Title</label>
                                            <select name="position" class="form-select" id="position-select">
                                                <option value="">Select Position</option>
                                                @foreach($positions as $position)
                                                <option value="{{ $position->title }}" data-code="{{ $position->code }}" data-dept="{{ $position->department_id }}">
                                                    {{ $position->title }}@if($position->department) - {{ $position->department->name }}@endif
                                                </option>
                                                @endforeach
                                                <option value="__custom__">-- Enter Custom Position --</option>
                                            </select>
                                            <input type="text" name="position_custom" id="position-custom" class="form-control mt-2 d-none" placeholder="Enter custom position">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Employment Type</label>
                                            <select name="employment_type" class="form-select">
                                                <option value="">Select Type</option>
                                                <option value="permanent">Permanent</option>
                                                <option value="contract">Contract</option>
                                                <option value="intern">Intern</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Salary</label>
                                            <input type="number" name="salary" class="form-control" min="0" step="0.01">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Assign Roles</label>
                                            <select name="roles[]" class="form-select" multiple>
                                                @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Emergency Contact -->
                        <div class="registration-step d-none" id="step-emergency" data-stage="emergency">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-phone-call me-2"></i>Step 3: Emergency Contact
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addEmergencyContact()">
                                            <i class="bx bx-plus"></i> Add Contact
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="emergencyContactsList">
                                        <!-- Emergency contacts will be added here dynamically -->
                                    </div>
                                    <div id="noEmergencyContactsMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-phone-call" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No emergency contacts added yet. Click "Add Contact" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Family Information -->
                        <div class="registration-step d-none" id="step-family" data-stage="family">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-group me-2"></i>Step 4: Family Information
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addFamilyMember()">
                                            <i class="bx bx-plus"></i> Add Member
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="familyMembers">
                                        <!-- Family members will be added here dynamically -->
                                    </div>
                                    <div id="noFamilyMembersMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-group" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No family members added yet. Click "Add Member" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Next of Kin -->
                        <div class="registration-step d-none" id="step-next-of-kin" data-stage="next-of-kin">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-user-check me-2"></i>Step 5: Next of Kin
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addNextOfKin()">
                                            <i class="bx bx-plus"></i> Add Contact
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="nextOfKinMembers">
                                        <!-- Next of kin will be added here dynamically -->
                                    </div>
                                    <div id="noNextOfKinMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-user-check" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No next of kin added yet. Click "Add Contact" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Referees -->
                        <div class="registration-step d-none" id="step-referees" data-stage="referees">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-user-voice me-2"></i>Step 6: Referees
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addReferee()">
                                            <i class="bx bx-plus"></i> Add Referee
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="refereesList">
                                        <!-- Referees will be added here dynamically -->
                                    </div>
                                    <div id="noRefereesMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-user-voice" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No referees added yet. Click "Add Referee" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7: Education -->
                        <div class="registration-step d-none" id="step-education" data-stage="education">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-book me-2"></i>Step 7: Education Background
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addEducation()">
                                            <i class="bx bx-plus"></i> Add Education
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="educationList">
                                        <!-- Education entries will be added here dynamically -->
                                    </div>
                                    <div id="noEducationMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-book" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No education entries added yet. Click "Add Education" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 8: Banking Information -->
                        <div class="registration-step d-none" id="step-banking" data-stage="banking">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-credit-card me-2"></i>Step 8: Banking Information
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addBankAccount()">
                                            <i class="bx bx-plus"></i> Add Account
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="bankAccountsList">
                                        <!-- Bank accounts will be added here dynamically -->
                                    </div>
                                    <div id="noBankAccountsMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-credit-card" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No bank accounts added yet. Click "Add Account" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 9: Deductions (Optional) -->
                        <div class="registration-step d-none" id="step-deductions" data-stage="deductions">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="bx bx-money me-2"></i>Step 9: Salary Deductions
                                            </h5>
                                            <small class="text-muted">Optional - Employee may or may not have deductions</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addDeduction()">
                                            <i class="bx bx-plus"></i> Add Deduction
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Salary deductions are optional. You can skip this step if the employee has no deductions, or add multiple deductions as needed.
                                    </div>
                                    <div id="deductionsList">
                                        <!-- Deductions will be added here dynamically -->
                                    </div>
                                    <div class="text-center mt-3 deduction-add-button">
                                        <button type="button" class="btn btn-outline-primary" onclick="addDeduction()">
                                            <i class="bx bx-plus me-1"></i> Add First Deduction
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 10: Profile Information -->
                        <div class="registration-step d-none" id="step-profile" data-stage="profile">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user-circle me-2"></i>Step 10: Profile Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12 text-center mb-4">
                                            <div class="mb-3">
                                                @if(isset($employee) && $employee->photo)
                                                <img src="{{ asset('storage/photos/' . $employee->photo) }}" alt="Profile Photo" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                                @else
                                                <div class="avatar avatar-xl mx-auto">
                                                    <span class="avatar-initial rounded-circle bg-label-primary" style="width: 150px; height: 150px; font-size: 60px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bx bx-user"></i>
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            <small class="text-muted">Max size: 5MB. Formats: JPEG, PNG, JPG, GIF, WEBP</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Marital Status</label>
                                            <select name="marital_status" class="form-select">
                                                <option value="">Select Status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nationality</label>
                                            <input type="text" name="nationality" class="form-control" placeholder="e.g., Tanzanian">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address</label>
                                            <textarea name="address" class="form-control" rows="3" placeholder="Full residential address"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 11: Documents -->
                        <div class="registration-step d-none" id="step-documents" data-stage="documents">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-file-blank me-2"></i>Step 11: Documents
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addDocument()">
                                            <i class="bx bx-plus"></i> Add Document
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Documents are optional. You can add documents now or later. Click "Add Document" to add a new document to the list.
                                    </div>
                                    <div id="documentsList">
                                        <!-- Documents will be added here dynamically -->
                                    </div>
                                    <div id="noDocumentsMessage" class="text-center text-muted py-4" style="display: block;">
                                        <i class="bx bx-file-blank" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No documents added yet. Click "Add Document" to add one.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 12: Statutory Information -->
                        <div class="registration-step d-none" id="step-statutory" data-stage="statutory">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-id-card me-2"></i>Step 12: Statutory Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">TIN Number</label>
                                            <input type="text" name="tin_number" class="form-control" placeholder="Tax Identification Number">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">NSSF Number</label>
                                            <input type="text" name="nssf_number" class="form-control" placeholder="National Social Security Fund Number">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">NHIF Number</label>
                                            <input type="text" name="nhif_number" class="form-control" placeholder="National Health Insurance Fund Number">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">HESLB Number</label>
                                            <input type="text" name="heslb_number" class="form-control" placeholder="Higher Education Students' Loans Board Number">
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="has_student_loan" value="1" id="has_student_loan">
                                                <label class="form-check-label" for="has_student_loan">
                                                    Has Student Loan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">
                                <i class="bx bx-chevron-left me-1"></i> Previous
                            </button>
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="saveStepBtn" onclick="saveCurrentStep()">
                                    <i class="bx bx-save me-1"></i> Save This Step
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                                    Next <i class="bx bx-chevron-right ms-1"></i>
                                </button>
                                <button type="button" class="btn btn-success d-none" id="completeBtn" onclick="completeRegistration()">
                                    <i class="bx bx-check me-1"></i> Complete Registration
                                </button>
                                <button type="button" class="btn btn-warning d-none" id="submitBtn" onclick="submitRegistration()">
                                    <i class="bx bx-paper-plane me-1"></i> Submit & Finalize
                                </button>
                            </div>
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
let currentStepIndex = 0;
const steps = ['personal', 'employment', 'emergency', 'family', 'next-of-kin', 'referees', 'education', 'banking', 'deductions', 'profile', 'documents', 'statutory'];
let emergencyContactIndex = 0;
let familyIndex = 0;
let nextOfKinIndex = 0;
let refereeIndex = 0;
let educationIndex = 0;
let bankAccountIndex = 0;
let deductionIndex = 1;
let documentIndex = 0;
let userId = null;

$(document).ready(function() {
    updateProgress();
    updateStageIndicators();
    
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Handle position dropdown custom option
    $('#position-select').on('change', function() {
        if ($(this).val() === '__custom__') {
            $('#position-custom').removeClass('d-none').focus();
        } else {
            $('#position-custom').addClass('d-none').val('');
        }
    });
    
    // Handle position custom input - update select when typing
    $('#position-custom').on('input', function() {
        if ($(this).val().trim()) {
            $('#position-select').val('__custom__');
        }
    });
});

function updateProgress() {
    const progress = ((currentStepIndex + 1) / steps.length) * 100;
    $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress);
    $('#progressPercentage').text(Math.round(progress) + '%');
    $('#progressText').text(Math.round(progress) + '%');
    $('#progressBadge').text(Math.round(progress) + '% Complete');
}

function updateStageIndicators() {
    $('.stage-badge').each(function(index) {
        const stage = $(this).data('stage');
        if (index < currentStepIndex) {
            $(this).removeClass('bg-secondary bg-primary').addClass('bg-success');
            $(this).find('i').removeClass('bx-circle bx-check').addClass('bx-check');
        } else if (index === currentStepIndex) {
            $(this).removeClass('bg-secondary bg-success').addClass('bg-primary');
            $(this).find('i').removeClass('bx-circle').addClass('bx-check');
        } else {
            $(this).removeClass('bg-primary bg-success').addClass('bg-secondary');
            $(this).find('i').removeClass('bx-check').addClass('bx-circle');
        }
    });
}

function showStep(index) {
    $('.registration-step').addClass('d-none');
    $(`#step-${steps[index]}`).removeClass('d-none');
    $('#currentStage').val(steps[index]);
    
    // Update navigation buttons
    $('#prevBtn').toggle(index > 0);
    
    // Show review/complete/submit buttons on last step
    if (index === steps.length - 1) {
        if (userId) {
            $('#completeBtn').removeClass('d-none').html('<i class="bx bx-check me-1"></i> Review & Complete');
            $('#submitBtn').removeClass('d-none');
            $('#nextBtn').addClass('d-none');
            $('#saveStepBtn').removeClass('d-none');
        } else {
            $('#completeBtn').addClass('d-none');
            $('#submitBtn').addClass('d-none');
            $('#nextBtn').removeClass('d-none');
        }
    } else {
        $('#completeBtn').addClass('d-none');
        $('#submitBtn').addClass('d-none');
        $('#nextBtn').removeClass('d-none');
        $('#saveStepBtn').removeClass('d-none');
    }
    
    updateProgress();
    updateStageIndicators();
}

function nextStep() {
    if (currentStepIndex < steps.length - 1) {
        // Save current step before moving
        saveCurrentStep(false, function() {
            currentStepIndex++;
            showStep(currentStepIndex);
        });
    }
}

function previousStep() {
    if (currentStepIndex > 0) {
        currentStepIndex--;
        showStep(currentStepIndex);
    }
}

function saveCurrentStep(showMessage = true, callback = null) {
    const formData = new FormData($('#employeeRegistrationForm')[0]);
    formData.append('stage', steps[currentStepIndex]);
    formData.append('save_as_draft', '1');
    
    // Always set password to welcome123 in background (remove any password fields from form)
    formData.delete('password');
    formData.delete('password_confirmation');
    
    // Handle position - if custom is selected, use custom value
    if ($('#position-select').val() === '__custom__' && $('#position-custom').val().trim()) {
        formData.set('position', $('#position-custom').val().trim());
        formData.delete('position_custom');
    } else if ($('#position-select').val() && $('#position-select').val() !== '__custom__') {
        formData.set('position', $('#position-select').val());
        formData.delete('position_custom');
    }
    
    $.ajax({
        url: '{{ route("modules.hr.employees.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                if (response.user_id) {
                    userId = response.user_id;
                    $('#userId').val(userId);
                }
                
                // If can review, show review button
                if (response.can_review && currentStepIndex === steps.length - 1) {
                    $('#completeBtn').html('<i class="bx bx-check me-1"></i> Review & Complete').removeClass('d-none');
                }
                
                if (showMessage) {
                    showToast('success', response.message || 'Step saved successfully!');
                }
                
                if (callback) callback();
            } else {
                showToast('error', response.message || 'Failed to save step');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            let errorMsg = xhr.responseJSON?.message || 'Failed to save step';
            
            if (Object.keys(errors).length > 0) {
                errorMsg = 'Validation errors:\n' + Object.values(errors).flat().join('\n');
            }
            
            showToast('error', errorMsg);
        }
    });
}

function completeRegistration() {
    // Save current step first, then redirect to review
    saveCurrentStep(false, function() {
        if (userId) {
            window.location.href = `{{ route("modules.hr.employees.review", ":id") }}`.replace(':id', userId);
        } else {
            showToast('error', 'Please save the current step first');
        }
    });
}

function submitRegistration() {
    if (!userId) {
        showToast('error', 'Please complete all steps and save before submitting.');
        return;
    }
    
    Swal.fire({
        title: 'Submit & Finalize Registration?',
        html: `
            <div class="text-start">
                <p>This will:</p>
                <ul class="text-start">
                    <li>Activate the employee account</li>
                    <li>Send welcome SMS with login credentials</li>
                    <li>Send congratulations SMS</li>
                    <li>Finalize the registration process</li>
                </ul>
                <p class="text-warning"><strong>This action cannot be undone.</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit & Finalize',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ffc107',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch(`{{ route("modules.hr.employees.finalize", ":id") }}`.replace(':id', userId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to finalize registration');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Registration Finalized!',
                html: `
                    <div class="text-start">
                        <p>Employee registration has been successfully finalized!</p>
                        <ul class="text-start">
                            <li>Account activated</li>
                            <li>SMS notifications sent</li>
                        </ul>
                        <p class="mt-3">You will be redirected to the employee directory.</p>
                    </div>
                `,
                confirmButtonText: 'Go to Employee Directory',
                confirmButtonColor: '#696cff'
            }).then(() => {
                window.location.href = '{{ route("modules.hr.employees") }}';
            });
        }
    });
}

// Dynamic form additions
function addEmergencyContact() {
    $('#noEmergencyContactsMessage').hide();
    const html = `
        <div class="emergency-contact-item border rounded p-3 mb-3" data-index="${emergencyContactIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-phone-call me-1"></i>Emergency Contact ${emergencyContactIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeEmergencyContact($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Name</label>
                    <input type="text" name="emergency_contacts[${emergencyContactIndex}][name]" class="form-control" placeholder="Enter contact name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="emergency_contacts[${emergencyContactIndex}][phone]" class="form-control" placeholder="Enter phone number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_contacts[${emergencyContactIndex}][relationship]" class="form-control" placeholder="e.g., Spouse, Parent, Sibling">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="emergency_contacts[${emergencyContactIndex}][address]" class="form-control" placeholder="Enter address">
                </div>
            </div>
        </div>`;
    $('#emergencyContactsList').append(html);
    emergencyContactIndex++;
}

function removeEmergencyContact(button) {
    button.closest('.emergency-contact-item').remove();
    if ($('#emergencyContactsList .emergency-contact-item').length === 0) {
        $('#noEmergencyContactsMessage').show();
    }
}

function addFamilyMember() {
    $('#noFamilyMembersMessage').hide();
    const html = `
        <div class="family-member-item border rounded p-3 mb-3" data-index="${familyIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-group me-1"></i>Family Member ${familyIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFamilyMember($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="family[${familyIndex}][name]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="family[${familyIndex}][relationship]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="family[${familyIndex}][date_of_birth]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="family[${familyIndex}][gender]" class="form-select">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Occupation</label>
                    <input type="text" name="family[${familyIndex}][occupation]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="family[${familyIndex}][phone]" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="family[${familyIndex}][is_dependent]" value="1" id="dependent${familyIndex}">
                        <label class="form-check-label" for="dependent${familyIndex}">Is Dependent</label>
                    </div>
                </div>
            </div>
        </div>`;
    $('#familyMembers').append(html);
    familyIndex++;
}

function removeFamilyMember(button) {
    button.closest('.family-member-item').remove();
    if ($('#familyMembers .family-member-item').length === 0) {
        $('#noFamilyMembersMessage').show();
    }
}

function addNextOfKin() {
    $('#noNextOfKinMessage').hide();
    const html = `
        <div class="next-of-kin-item border rounded p-3 mb-3" data-index="${nextOfKinIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-user-check me-1"></i>Next of Kin ${nextOfKinIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeNextOfKin($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="next_of_kin[${nextOfKinIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                    <input type="text" name="next_of_kin[${nextOfKinIndex}][relationship]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="next_of_kin[${nextOfKinIndex}][phone]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="next_of_kin[${nextOfKinIndex}][email]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address <span class="text-danger">*</span></label>
                    <input type="text" name="next_of_kin[${nextOfKinIndex}][address]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="next_of_kin[${nextOfKinIndex}][id_number]" class="form-control">
                </div>
            </div>
        </div>`;
    $('#nextOfKinMembers').append(html);
    nextOfKinIndex++;
}

function removeNextOfKin(button) {
    button.closest('.next-of-kin-item').remove();
    if ($('#nextOfKinMembers .next-of-kin-item').length === 0) {
        $('#noNextOfKinMessage').show();
    }
}

function addReferee() {
    $('#noRefereesMessage').hide();
    const html = `
        <div class="referee-item border rounded p-3 mb-3" data-index="${refereeIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-user-voice me-1"></i>Referee ${refereeIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeReferee($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="referees[${refereeIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Position</label>
                    <input type="text" name="referees[${refereeIndex}][position]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Organization</label>
                    <input type="text" name="referees[${refereeIndex}][organization]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="referees[${refereeIndex}][phone]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="referees[${refereeIndex}][email]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="referees[${refereeIndex}][relationship]" class="form-control">
                </div>
            </div>
        </div>`;
    $('#refereesList').append(html);
    refereeIndex++;
}

function removeReferee(button) {
    button.closest('.referee-item').remove();
    if ($('#refereesList .referee-item').length === 0) {
        $('#noRefereesMessage').show();
    }
}

function addEducation() {
    $('#noEducationMessage').hide();
    const html = `
        <div class="education-item border rounded p-3 mb-3" data-index="${educationIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-book me-1"></i>Education ${educationIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeEducation($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Institution Name <span class="text-danger">*</span></label>
                    <input type="text" name="educations[${educationIndex}][institution_name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Qualification <span class="text-danger">*</span></label>
                    <input type="text" name="educations[${educationIndex}][qualification]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Field of Study</label>
                    <input type="text" name="educations[${educationIndex}][field_of_study]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Year</label>
                    <input type="number" name="educations[${educationIndex}][start_year]" class="form-control" min="1900" max="{{ date('Y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Year</label>
                    <input type="number" name="educations[${educationIndex}][end_year]" class="form-control" min="1900" max="{{ date('Y') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Grade</label>
                    <input type="text" name="educations[${educationIndex}][grade]" class="form-control">
                </div>
            </div>
        </div>`;
    $('#educationList').append(html);
    educationIndex++;
}

function removeEducation(button) {
    button.closest('.education-item').remove();
    if ($('#educationList .education-item').length === 0) {
        $('#noEducationMessage').show();
    }
}

function addBankAccount() {
    $('#noBankAccountsMessage').hide();
    const html = `
        <div class="bank-account-item border rounded p-3 mb-3" data-index="${bankAccountIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-credit-card me-1"></i>Bank Account ${bankAccountIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeBankAccount($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][bank_name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][account_number]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][account_name]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][branch_name]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SWIFT Code</label>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][swift_code]" class="form-control">
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="bank_accounts[${bankAccountIndex}][is_primary]" value="1" id="primaryBank${bankAccountIndex}">
                        <label class="form-check-label" for="primaryBank${bankAccountIndex}">Primary Account</label>
                    </div>
                </div>
            </div>
        </div>`;
    $('#bankAccountsList').append(html);
    bankAccountIndex++;
}

function removeBankAccount(button) {
    button.closest('.bank-account-item').remove();
    if ($('#bankAccountsList .bank-account-item').length === 0) {
        $('#noBankAccountsMessage').show();
    }
}

function addDeduction() {
    const html = `
        <div class="deduction-item border rounded p-3 mb-3" data-index="${deductionIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Deduction ${deductionIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.deduction-item').remove(); updateDeductionButtons();">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Deduction Type</label>
                    <input type="text" name="deductions[${deductionIndex}][deduction_type]" class="form-control" placeholder="e.g., Loan, Advance, Insurance">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount</label>
                    <input type="number" name="deductions[${deductionIndex}][amount]" class="form-control" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Frequency</label>
                    <select name="deductions[${deductionIndex}][frequency]" class="form-select">
                        <option value="monthly">Monthly</option>
                        <option value="one-time">One-Time</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="deductions[${deductionIndex}][start_date]" class="form-control deduction-start-date" onchange="validateDeductionDates(${deductionIndex})">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="deductions[${deductionIndex}][end_date]" class="form-control deduction-end-date" onchange="validateDeductionDates(${deductionIndex})">
                    <div class="deduction-date-error text-danger small mt-1" style="display: none;">End date must be after start date</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="deductions[${deductionIndex}][description]" class="form-control" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="deductions[${deductionIndex}][is_active]" value="1" id="activeDeduction${deductionIndex}" checked>
                        <label class="form-check-label" for="activeDeduction${deductionIndex}">Active</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" name="deductions[${deductionIndex}][notes]" class="form-control" placeholder="Optional notes">
                </div>
            </div>
        </div>`;
    $('#deductionsList').append(html);
    deductionIndex++;
    updateDeductionButtons();
}

function updateDeductionButtons() {
    const hasDeductions = $('.deduction-item').length > 0;
    if (hasDeductions) {
        $('.deduction-add-button').hide();
    } else {
        $('.deduction-add-button').show();
    }
}

function addDocument() {
    // Hide "no documents" message
    $('#noDocumentsMessage').hide();
    
    const html = `
        <div class="document-item border rounded p-3 mb-3" data-index="${documentIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-file me-1"></i>Document ${documentIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDocument($(this))">
                    <i class="bx bx-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Document Type</label>
                    <select name="documents[${documentIndex}][document_type]" class="form-select">
                        <option value="">Select Type</option>
                        <option value="ID Card">ID Card</option>
                        <option value="Passport">Passport</option>
                        <option value="Birth Certificate">Birth Certificate</option>
                        <option value="Academic Certificate">Academic Certificate</option>
                        <option value="Professional License">Professional License</option>
                        <option value="Contract">Contract</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Document Name</label>
                    <input type="text" name="documents[${documentIndex}][document_name]" class="form-control" placeholder="Enter document name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Document Number</label>
                    <input type="text" name="documents[${documentIndex}][document_number]" class="form-control" placeholder="Enter document number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">File (Optional)</label>
                    <input type="file" name="documents[${documentIndex}][file]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted">Max: 10MB. Formats: PDF, DOC, DOCX, JPG, PNG</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="documents[${documentIndex}][issue_date]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="documents[${documentIndex}][expiry_date]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issued By</label>
                    <input type="text" name="documents[${documentIndex}][issued_by]" class="form-control" placeholder="Enter issuer name">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="documents[${documentIndex}][description]" class="form-control" rows="2" placeholder="Enter description (optional)"></textarea>
                </div>
            </div>
        </div>`;
    $('#documentsList').append(html);
    documentIndex++;
}

function removeDocument(button) {
    button.closest('.document-item').remove();
    // Show "no documents" message if list is empty
    if ($('#documentsList .document-item').length === 0) {
        $('#noDocumentsMessage').show();
    }
}

function showToast(type, message) {
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-warning';
    const toast = $(`
        <div class="alert ${bgClass} alert-dismissible fade show position-fixed top-0 end-0 m-3 text-white" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    `);
    $('body').append(toast);
    setTimeout(() => toast.alert('close'), 5000);
}
</script>
@endpush

