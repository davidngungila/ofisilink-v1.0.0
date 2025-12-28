@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Advanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-white text-primary">
                                        <i class="bx bx-user-circle fs-1"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="card-title text-white mb-1">Employee Management System</h4>
                                    <p class="card-text text-white-50 mb-0">
                                        @if($canViewAll)
                                            Comprehensive employee database management with advanced analytics and reporting
                                        @else
                                            Personal employee profile and information management
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @if($canViewAll)
                                <div class="dropdown" style="position: relative; z-index: 1060;">
                                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="z-index: 1061;">
                                        <i class="bx bx-dots-vertical-rounded me-1"></i>Quick Actions
                                    </button>
                                    <ul class="dropdown-menu" style="z-index: 1062; position: absolute;">
                                        <li><a class="dropdown-item" href="{{ route('modules.hr.employees.register') }}">
                                            <i class="bx bx-user-plus me-2"></i>Register New Employee
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportEmployees()">
                                            <i class="bx bx-download me-2"></i>Export All
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="bulkActions()">
                                            <i class="bx bx-check-square me-2"></i>Bulk Actions
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateReport()">
                                            <i class="bx bx-bar-chart me-2"></i>Generate Report
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-warning" href="#" onclick="bulkGeneratePasswordsAndSendSMS()">
                                            <i class="bx bx-key me-2"></i>Generate Passwords & Send SMS to All
                                        </a></li>
                                    </ul>
                                </div>
                                @endif
                                <button class="btn btn-light" onclick="refreshData()">
                                    <i class="bx bx-refresh me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter and Search Panel -->
    @if($canViewAll)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-filter-alt me-2"></i>Advanced Filters & Search
                        </h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                            <i class="bx bx-chevron-down me-1"></i>Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="collapse" id="filterPanel">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search Employee</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="advancedSearch" placeholder="Name, ID, email...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Employment Type</label>
                                <select class="form-select" id="employmentTypeFilter">
                                    <option value="">All Types</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                    <option value="intern">Intern</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Salary Range</label>
                                <select class="form-select" id="salaryRangeFilter">
                                    <option value="">All Ranges</option>
                                    <option value="0-500000">0 - 500K</option>
                                    <option value="500000-1000000">500K - 1M</option>
                                    <option value="1000000-2000000">1M - 2M</option>
                                    <option value="2000000+">2M+</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary w-100" onclick="applyFilters()">
                                    <i class="bx bx-filter"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Area -->
    <div class="row">
        @if($canViewAll)
            @include('modules.hr.employees-manager')
        @else
            @include('modules.hr.employees-staff')
        @endif
    </div>
</div>

<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="z-index: 1061;">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h5 class="modal-title mb-0" id="employeeDetailsModalTitle">
                        <i class="bx bx-user me-2"></i>Employee Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            </div>
            <div class="modal-body p-0">
                <!-- Profile Completion Progress -->
                <div class="card border-0 rounded-0 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0 text-primary">
                                    <i class="bx bx-tachometer me-2"></i>Profile Completion
                                </h6>
                                <small class="text-muted">Data completeness indicator</small>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-primary" id="viewCompletionPercentage">0%</h3>
                                <small class="text-muted">Complete</small>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped bg-primary" 
                                 role="progressbar" 
                                 id="viewProgressBar" 
                                 style="width: 0%;"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span class="progress-text" id="viewProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content will be loaded via AJAX -->
                <div id="employeeDetailsContent" class="p-4">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading employee details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="editEmployeeBtn" style="display: none;">
                    <i class="bx bx-edit me-2"></i>Edit Employee
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal with Tabs -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1070;">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content" style="z-index: 1071; display: flex; flex-direction: column; height: 100vh;">
            <div class="modal-header bg-primary text-white" style="flex-shrink: 0;">
                <div class="d-flex align-items-center w-100">
                    <h5 class="modal-title mb-0" id="editEmployeeModalTitle">
                        <i class="bx bx-edit me-2"></i>Edit Employee Information
                </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            </div>
            <form id="editEmployeeForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="edit_employee_user_id" name="user_id">
                <input type="hidden" id="edit_current_section" name="section" value="personal">
                
                <!-- Profile Completion Progress -->
                <div class="border-bottom bg-light" style="flex-shrink: 0;">
                    <div class="px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0 text-primary">
                                    <i class="bx bx-tachometer me-2"></i>Profile Completion
                                </h6>
                                <small class="text-muted">Edit sections to improve completion</small>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-primary" id="editCompletionPercentage">0%</h3>
                                <small class="text-muted" id="editCompletionStatus">Incomplete</small>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped bg-primary" 
                                 role="progressbar" 
                                 id="editProgressBar" 
                                 style="width: 0%;"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span class="progress-text fw-bold" id="editProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Navigation - Fixed at top -->
                <div class="border-bottom" style="flex-shrink: 0; background-color: #f8f9fa;">
                    <ul class="nav nav-tabs nav-tabs-lg px-3 mb-0" id="editTabs" role="tablist" style="flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="edit-personal-tab" data-bs-toggle="tab" data-bs-target="#edit-personal" type="button" onclick="switchEditTab('personal')">
                                <i class="bx bx-user me-1"></i><span class="d-none d-md-inline">Personal</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-employment-tab" data-bs-toggle="tab" data-bs-target="#edit-employment" type="button" onclick="switchEditTab('employment')">
                                <i class="bx bx-briefcase me-1"></i><span class="d-none d-md-inline">Employment</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-emergency-tab" data-bs-toggle="tab" data-bs-target="#edit-emergency" type="button" onclick="switchEditTab('emergency')">
                                <i class="bx bx-phone-call me-1"></i><span class="d-none d-md-inline">Emergency</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-family-tab" data-bs-toggle="tab" data-bs-target="#edit-family" type="button" onclick="switchEditTab('family')">
                                <i class="bx bx-group me-1"></i><span class="d-none d-md-inline">Family</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-next-of-kin-tab" data-bs-toggle="tab" data-bs-target="#edit-next-of-kin" type="button" onclick="switchEditTab('next-of-kin')">
                                <i class="bx bx-user-check me-1"></i><span class="d-none d-md-inline">Next of Kin</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-referees-tab" data-bs-toggle="tab" data-bs-target="#edit-referees" type="button" onclick="switchEditTab('referees')">
                                <i class="bx bx-file me-1"></i><span class="d-none d-md-inline">Referees</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-education-tab" data-bs-toggle="tab" data-bs-target="#edit-education" type="button" onclick="switchEditTab('education')">
                                <i class="bx bx-book me-1"></i><span class="d-none d-md-inline">Education</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-bank-tab" data-bs-toggle="tab" data-bs-target="#edit-bank" type="button" onclick="switchEditTab('bank')">
                                <i class="bx bx-credit-card me-1"></i><span class="d-none d-md-inline">Bank</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-statutory-tab" data-bs-toggle="tab" data-bs-target="#edit-statutory" type="button" onclick="switchEditTab('statutory')">
                                <i class="bx bx-receipt me-1"></i><span class="d-none d-md-inline">Deductions</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-images-tab" data-bs-toggle="tab" data-bs-target="#edit-images" type="button" onclick="switchEditTab('images')">
                                <i class="bx bx-image me-1"></i><span class="d-none d-md-inline">Profile</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-documents-tab" data-bs-toggle="tab" data-bs-target="#edit-documents" type="button" onclick="switchEditTab('documents')">
                                <i class="bx bx-folder me-1"></i><span class="d-none d-md-inline">Documents</span>
                            </button>
                        </li>
                    </ul>
                </div>
                    
                <!-- Tab Content - Scrollable -->
                <div class="modal-body p-4" id="editModalBodyContent" style="flex: 1; overflow-y: auto; overflow-x: hidden;">
                    <div class="tab-content" id="editTabContent">
                        <!-- Content loaded via JS based on selected tab -->
                    </div>
                </div>
                
                <!-- Footer - Fixed at bottom -->
                <div class="modal-footer border-top bg-light" style="flex-shrink: 0; padding: 1rem;">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <small class="text-muted" id="saveStatusText"></small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i>Cancel
                    </button>
                            <button type="button" class="btn btn-outline-primary me-2" id="saveCurrentSectionBtn" onclick="saveCurrentSection()">
                                <i class="bx bx-save me-1"></i>Save Section
                            </button>
                            <button type="button" class="btn btn-primary" id="saveAllSectionsBtn" onclick="saveAllSections()">
                                <i class="bx bx-check me-1"></i>Save All
                    </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Photo Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadPhotoModalTitle">Upload Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadPhotoForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Select Photo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                        <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 5MB</div>
                                </div>
                    <div class="text-center">
                        <img id="photoPreview" src="" alt="Photo Preview" class="img-thumbnail" style="max-width: 200px; display: none; border-radius: 50%;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload me-2"></i>Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Employee Modal with Tabs -->
@if($canEditAll)
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addEmployeeModalTitle">
                    <i class="bx bx-user-plus me-2"></i>Add New Employee
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEmployeeForm">
                <input type="hidden" id="add_user_id" name="user_id" value="">
                <input type="hidden" id="current_stage" name="stage" value="personal">
                <div class="modal-body" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                    <!-- Enhanced Progress Indicator -->
                    <div class="card mb-4 border-primary">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0 text-primary">
                                        <i class="bx bx-tachometer me-2"></i>Registration Progress
                                    </h6>
                                    <small class="text-muted">Complete each stage to finish registration</small>
                        </div>
                                <div class="text-end">
                                    <h3 class="mb-0 text-primary" id="progressPercentage">11%</h3>
                                    <small class="text-muted" id="stageProgress">Step 1 of 9</small>
                                </div>
                            </div>
                            <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" 
                                     id="progressBar" 
                                     style="width: 11.11%;"
                                     aria-valuenow="11" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span class="progress-text" id="progressText">11%</span>
                                </div>
                            </div>
                            <!-- Stage Indicators -->
                            <div class="row g-2 mt-2" id="stageIndicators">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-primary" data-stage="0" data-stage-name="personal">
                                            <i class="bx bx-check me-1"></i>Personal
                                        </span>
                                        <span class="badge bg-secondary" data-stage="1" data-stage-name="employment">
                                            <i class="bx bx-circle me-1"></i>Employment
                                        </span>
                                        <span class="badge bg-secondary" data-stage="2" data-stage-name="emergency">
                                            <i class="bx bx-circle me-1"></i>Emergency
                                        </span>
                                        <span class="badge bg-secondary" data-stage="3" data-stage-name="family">
                                            <i class="bx bx-circle me-1"></i>Family
                                        </span>
                                        <span class="badge bg-secondary" data-stage="4" data-stage-name="next-of-kin">
                                            <i class="bx bx-circle me-1"></i>Next of Kin
                                        </span>
                                        <span class="badge bg-secondary" data-stage="5" data-stage-name="referees">
                                            <i class="bx bx-circle me-1"></i>Referees
                                        </span>
                                        <span class="badge bg-secondary" data-stage="6" data-stage-name="education">
                                            <i class="bx bx-circle me-1"></i>Education
                                        </span>
                                        <span class="badge bg-secondary" data-stage="7" data-stage-name="banking">
                                            <i class="bx bx-circle me-1"></i>Banking
                                        </span>
                                        <span class="badge bg-secondary" data-stage="8" data-stage-name="statutory">
                                            <i class="bx bx-circle me-1"></i>Statutory
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-fill mb-4" id="employeeTabs" role="tablist" style="overflow-x: auto; flex-wrap: nowrap;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab-btn" data-bs-toggle="tab" data-bs-target="#personal-tab-pane" type="button" role="tab" onclick="switchTab('personal')">
                                <i class="bx bx-user me-1"></i>Personal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employment-tab-btn" data-bs-toggle="tab" data-bs-target="#employment-tab-pane" type="button" role="tab" onclick="switchTab('employment')">
                                <i class="bx bx-briefcase me-1"></i>Employment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="emergency-tab-btn" data-bs-toggle="tab" data-bs-target="#emergency-tab-pane" type="button" role="tab" onclick="switchTab('emergency')">
                                <i class="bx bx-phone-call me-1"></i>Emergency
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="family-tab-btn" data-bs-toggle="tab" data-bs-target="#family-tab-pane" type="button" role="tab" onclick="switchTab('family')">
                                <i class="bx bx-group me-1"></i>Family
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="next-of-kin-tab-btn" data-bs-toggle="tab" data-bs-target="#next-of-kin-tab-pane" type="button" role="tab" onclick="switchTab('next-of-kin')">
                                <i class="bx bx-user-check me-1"></i>Next of Kin
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="referees-tab-btn" data-bs-toggle="tab" data-bs-target="#referees-tab-pane" type="button" role="tab" onclick="switchTab('referees')">
                                <i class="bx bx-user-voice me-1"></i>Referees
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="education-tab-btn" data-bs-toggle="tab" data-bs-target="#education-tab-pane" type="button" role="tab" onclick="switchTab('education')">
                                <i class="bx bx-book me-1"></i>Education
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="banking-tab-btn" data-bs-toggle="tab" data-bs-target="#banking-tab-pane" type="button" role="tab" onclick="switchTab('banking')">
                                <i class="bx bx-credit-card me-1"></i>Banking
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="statutory-tab-btn" data-bs-toggle="tab" data-bs-target="#statutory-tab-pane" type="button" role="tab" onclick="switchTab('statutory')">
                                <i class="bx bx-receipt me-1"></i>Statutory
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="employeeTabContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal-tab-pane" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_name" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="add_email" name="email" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="add_phone" name="phone">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_hire_date" class="form-label">Hire Date</label>
                                        <input type="date" class="form-control" id="add_hire_date" name="hire_date">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_primary_department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="add_primary_department_id" name="primary_department_id" required>
                                                    <option value="">Select Department</option>
                                                    @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="add_password" name="password" required>
                                        <div class="form-text">Minimum 8 characters</div>
                                        </div>
                                    </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>
                                </div>
                        
                        <!-- Employment Information Tab -->
                        <div class="tab-pane fade" id="employment-tab-pane" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_position" class="form-label">Position</label>
                                        <select class="form-select" id="add_position" name="position">
                                            <option value="">Select Position</option>
                                            @foreach($positions as $position)
                                            <option value="{{ $position->title }}" data-code="{{ $position->code }}" data-dept="{{ $position->department_id }}">
                                                {{ $position->title }}@if($position->department) - {{ $position->department->name }}@endif
                                            </option>
                                            @endforeach
                                            <option value="__custom__">-- Enter Custom Position --</option>
                                        </select>
                                        <input type="text" class="form-control mt-2 d-none" id="add_position_custom" name="position_custom" placeholder="Enter custom position">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                        <label for="add_employment_type" class="form-label">Employment Type</label>
                                        <select class="form-select" id="add_employment_type" name="employment_type">
                                                    <option value="permanent">Permanent</option>
                                                    <option value="contract">Contract</option>
                                                    <option value="intern">Intern</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                <label for="add_salary" class="form-label">Basic Salary (TZS)</label>
                                <input type="number" class="form-control" id="add_salary" name="salary" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="mb-3">
                                <label class="form-label">Assign Roles</label>
                                        <div class="row">
                                            @foreach($roles as $role)
                                    <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="add_role_{{ $role->id }}">
                                            <label class="form-check-label" for="add_role_{{ $role->id }}">
                                                        {{ $role->display_name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                        
                        <!-- Banking Information Tab -->
                        <div class="tab-pane fade" id="banking-tab-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="add_bank_name" class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" id="add_bank_name" name="bank_name" placeholder="e.g., CRDB Bank, NMB Bank">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="add_bank_account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="add_bank_account_number" name="bank_account_number" placeholder="Enter account number">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax & Statutory Information Tab -->
                        <div class="tab-pane fade" id="statutory-tab-pane" role="tabpanel">
                            <div class="row">
                        <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_tin_number" class="form-label">TIN Number</label>
                                        <input type="text" class="form-control" id="add_tin_number" name="tin_number">
                                    </div>
                                    </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_nssf_number" class="form-label">NSSF Number</label>
                                        <input type="text" class="form-control" id="add_nssf_number" name="nssf_number">
                                    </div>
                                    </div>
                                        </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_nhif_number" class="form-label">NHIF Number</label>
                                        <input type="text" class="form-control" id="add_nhif_number" name="nhif_number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_heslb_number" class="form-label">HESLB Number</label>
                                        <input type="text" class="form-control" id="add_heslb_number" name="heslb_number">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_has_student_loan" name="has_student_loan" value="1">
                                    <label class="form-check-label" for="add_has_student_loan">
                                        Has Student Loan (HESLB)
                                    </label>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn" onclick="saveDraft()">
                        <i class="bx bx-save me-2"></i>Save Draft
                    </button>
                    <button type="button" class="btn btn-outline-info" id="saveCurrentBtn" onclick="saveEmployeeStage(stages[currentStageIndex], false)">
                        <i class="bx bx-save me-2"></i>Save Current Stage
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="prevStageBtn" onclick="previousStage()" style="display: none;">
                        <i class="bx bx-chevron-left me-2"></i>Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextStageBtn" onclick="nextStage()">
                        Save & Next <i class="bx bx-chevron-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="completeBtn" onclick="saveEmployeeStage(stages[currentStageIndex], false)" style="display: none;">
                        <i class="bx bx-check me-2"></i>Complete & Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Edit employee form submission
    $('#editEmployeeForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const employeeId = $('#editEmployeeModal').data('employee-id');
        
        Swal.fire({
            title: 'Update Employee?',
            text: 'Are you sure you want to update this employee\'s information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                updateEmployee(employeeId, formData);
            }
        });
    });
    
    // Add employee form submission
    @if($canEditAll)
    $('#addEmployeeForm').submit(function(e) {
        e.preventDefault();
        console.log('Form submit prevented, calling saveEmployeeStage');
        const stage = $('#current_stage').val() || stages[currentStageIndex];
        saveEmployeeStage(stage, false);
    });
    
    // Initialize tabs
    let currentStageIndex = 0;
    const stages = ['personal', 'employment', 'emergency', 'family', 'next-of-kin', 'referees', 'education', 'banking', 'statutory'];
    const savedStages = new Set(); // Track which stages have been saved
    
    // Make sure functions and variables are accessible globally
    window.nextStage = nextStage;
    window.saveEmployeeStage = saveEmployeeStage;
    window.validateStage = validateStage;
    window.currentStageIndex = currentStageIndex;
    window.stages = stages;
    
    function switchTab(stage) {
        const index = stages.indexOf(stage);
        if (index !== -1) {
            // Check if previous stage is saved before allowing navigation
            if (index > currentStageIndex && !savedStages.has(stages[currentStageIndex])) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stage Not Saved',
                    text: 'Please save the current stage before proceeding to the next one.',
                    confirmButtonText: 'Save & Continue',
                    showCancelButton: true,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Auto-save current stage first
                        saveEmployeeStage(stages[currentStageIndex], true);
                    }
                });
                return false;
            }
            
            currentStageIndex = index;
            window.currentStageIndex = index; // Sync with global
            $('#current_stage').val(stage);
            updateProgress();
            updateButtons();
            updateTabIndicators();
        }
        return true;
    }
    
    function updateTabIndicators() {
        // Add checkmark to saved stages
        stages.forEach((stage, index) => {
            const tabBtn = $(`#${stage}-tab-btn`);
            if (savedStages.has(stage)) {
                tabBtn.addClass('saved-stage');
                if (!tabBtn.find('.check-icon').length) {
                    tabBtn.append(' <i class="bx bx-check check-icon text-success"></i>');
                }
            } else {
                tabBtn.removeClass('saved-stage');
                tabBtn.find('.check-icon').remove();
            }
        });
    }
    
    function updateProgress(completionPercentage = null) {
        const totalStages = stages.length;
        const currentStep = currentStageIndex + 1;
        
        // Use provided completion percentage if available, otherwise calculate based on stage progress
        let progress = completionPercentage !== null ? completionPercentage : (currentStep / totalStages) * 100;
        const progressRounded = Math.round(progress);
        
        // Update progress bar
        $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progressRounded);
        $('#progressText').text(progressRounded + '%');
        $('#progressPercentage').text(progressRounded + '%');
        $('#stageProgress').text(`Step ${currentStep} of ${totalStages}`);
        
        // Update progress bar color based on completion
        $('#progressBar').removeClass('bg-primary bg-success bg-warning bg-danger');
        if (progress >= 90) {
            $('#progressBar').addClass('bg-success');
        } else if (progress >= 70) {
            $('#progressBar').addClass('bg-primary');
        } else if (progress >= 50) {
            $('#progressBar').addClass('bg-warning');
        } else {
            $('#progressBar').addClass('bg-danger');
        }
        
        // Update stage indicators
        $('[data-stage]').each(function() {
            const stageIndex = parseInt($(this).data('stage'));
            const stageName = $(this).data('stage-name');
            const isCompleted = savedStages.has(stageName);
            const isCurrent = stageIndex === currentStageIndex;
            const isPast = stageIndex < currentStageIndex;
            
            $(this).removeClass('bg-primary bg-success bg-warning bg-secondary');
            
            if (isCompleted) {
                $(this).addClass('bg-success').html(`<i class="bx bx-check me-1"></i>${getStageDisplayName(stageName)}`);
            } else if (isCurrent) {
                $(this).addClass('bg-primary').html(`<i class="bx bx-loader-alt bx-spin me-1"></i>${getStageDisplayName(stageName)}`);
            } else if (isPast) {
                $(this).addClass('bg-warning').html(`<i class="bx bx-time me-1"></i>${getStageDisplayName(stageName)}`);
            } else {
                $(this).addClass('bg-secondary').html(`<i class="bx bx-circle me-1"></i>${getStageDisplayName(stageName)}`);
            }
        });
        
        // Update button visibility
        const isFirst = currentStageIndex === 0;
        const isLast = currentStageIndex === stages.length - 1;
        $('#prevStageBtn').toggle(!isFirst);
        $('#nextStageBtn').toggle(!isLast);
        $('#completeBtn').toggle(isLast);
    }
    
    function getStageDisplayName(stage) {
        const names = {
            'personal': 'Personal',
            'employment': 'Employment',
            'emergency': 'Emergency',
            'family': 'Family',
            'next-of-kin': 'Next of Kin',
            'referees': 'Referees',
            'education': 'Education',
            'banking': 'Banking',
            'statutory': 'Statutory'
        };
        return names[stage] || stage;
    }
    
    function updateTabIndicators() {
        // Update tab badges to show completion status
        stages.forEach((stage, index) => {
            const tabBtn = $(`#${stage}-tab-btn`);
            if (savedStages.has(stage)) {
                tabBtn.find('i').removeClass('bx-circle').addClass('bx-check-circle text-success');
            }
        });
    }
    
    function updateButtons() {
        const isFirst = currentStageIndex === 0;
        const isLast = currentStageIndex === stages.length - 1;
        
        $('#prevStageBtn').toggle(!isFirst);
        $('#nextStageBtn').toggle(!isLast);
        $('#completeBtn').toggle(isLast);
    }
    
    function nextStage() {
        console.log('nextStage called, current stage:', stages[currentStageIndex], 'index:', currentStageIndex);
        
        const currentStage = stages[currentStageIndex];
        
        if (!currentStage) {
            console.error('No current stage found');
            Swal.fire('Error', 'Unable to determine current stage.', 'error');
            return;
        }
        
        // Validate current stage before moving (but don't block for optional stages)
        const validationResult = validateStage(currentStage);
        console.log('Validation result:', validationResult);
        
        if (!validationResult) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please fill all required fields before proceeding.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Save and move to next
        console.log('Calling saveEmployeeStage with moveNext=true');
        saveEmployeeStage(currentStage, true);
    }
    
    function previousStage() {
        if (currentStageIndex > 0) {
            currentStageIndex--;
            window.currentStageIndex = currentStageIndex; // Sync with global
            const stage = stages[currentStageIndex];
            if (switchTab(stage)) {
                $(`#${stage}-tab-btn`).tab('show');
            } else {
                // Restore index if switch was blocked
                currentStageIndex++;
            }
        }
    }
    
    function validateStage(stage) {
        let isValid = true;
        const requiredFields = {
            'personal': ['name', 'email'],
            'employment': ['primary_department_id'],
            'emergency': [], // Optional
            'family': [], // Optional
            'next-of-kin': [], // Optional
            'referees': [], // Optional
            'education': [], // Optional
            'banking': [], // Optional
            'statutory': [] // Optional
        };
        
        const fields = requiredFields[stage] || [];
        fields.forEach(field => {
            const input = $(`#add_${field}`).length ? $(`#add_${field}`) : $(`[name="${field}"]`);
            if (input.length && input.prop('required') && !input.val().trim()) {
                isValid = false;
                input.addClass('is-invalid');
                input.on('input', function() {
                    $(this).removeClass('is-invalid');
                });
            }
        });
        
        return isValid;
    }
    
    function saveDraft() {
        const currentStage = stages[currentStageIndex];
        saveEmployeeStage(currentStage, false, true);
    }
    
    function saveEmployeeStage(stage, moveNext = false, isDraft = false) {
        console.log('saveEmployeeStage called:', { stage, moveNext, isDraft });
        
        if (!stage) {
            console.error('No stage provided to saveEmployeeStage');
            Swal.fire('Error', 'Unable to determine stage.', 'error');
            return false;
        }
        
        // Validate before saving (skip validation for drafts)
        if (!isDraft) {
            const validationResult = validateStage(stage);
            console.log('Validation result for', stage, ':', validationResult);
            
            if (!validationResult) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill all required fields before saving.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }
        
        // Check if form exists
        const form = $('#addEmployeeForm');
        if (!form.length) {
            console.error('Form #addEmployeeForm not found');
            Swal.fire('Error', 'Form not found. Please refresh the page.', 'error');
            return false;
        }
        
        const formData = new FormData(form[0]);
        formData.append('stage', stage);
        formData.append('save_as_draft', isDraft ? '1' : '0');
        
        // Always set password to welcome123 in background (remove any password fields)
        formData.delete('password');
        formData.delete('password_confirmation');
        
        // Handle position - if custom is selected, use custom value
        if (stage === 'employment') {
            const positionSelect = $('#add_position');
            const positionCustom = $('#add_position_custom');
            if (positionSelect.length && positionSelect.val() === '__custom__' && positionCustom.length && positionCustom.val().trim()) {
                formData.set('position', positionCustom.val().trim());
                formData.delete('position_custom');
            } else if (positionSelect.length && positionSelect.val() && positionSelect.val() !== '__custom__') {
                formData.set('position', positionSelect.val());
                formData.delete('position_custom');
            }
        }
        
        console.log('FormData prepared, sending AJAX request...');
        
        // Handle array data for each section
        if (stage === 'family') {
            collectFormArrayData('family', formData);
        } else if (stage === 'next-of-kin') {
            collectFormArrayData('next_of_kin', formData);
        } else if (stage === 'referees') {
            collectFormArrayData('referees', formData);
        } else if (stage === 'education') {
            collectFormArrayData('educations', formData);
        } else if (stage === 'banking') {
            collectFormArrayData('bank_accounts', formData);
            // Handle primary account selection
            const primaryAccount = $('#addBankAccountsList').find('input[name="primary_bank_account"]:checked').val();
            if (primaryAccount) {
                formData.append('primary_bank_account', primaryAccount);
            }
        }
        
        // Show loading
        const saveBtn = moveNext ? $('#nextStageBtn') : (isDraft ? $('#saveDraftBtn') : $('#saveCurrentBtn'));
        const originalText = saveBtn.html();
        const stageName = getStageDisplayName(stage);
        
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        // Disable all buttons during save
        $('#prevStageBtn, #saveDraftBtn, #saveCurrentBtn, #completeBtn').prop('disabled', true);
        
        // Set timeout warning
        const timeoutId = setTimeout(function() {
            if (saveBtn.prop('disabled')) {
                Swal.fire({
                    icon: 'info',
                    title: 'Saving...',
                    text: `${stageName} is being saved. Please wait...`,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        }, 10000); // 10 second warning
        
        $.ajax({
            url: `/employees`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 60000,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                console.log('Saving stage:', stage, 'Move next:', moveNext, 'Is draft:', isDraft);
            },
            success: function(response) {
                clearTimeout(timeoutId);
                console.log('Save response received:', response);
                
                // Re-enable buttons
                saveBtn.prop('disabled', false).html(originalText);
                $('#prevStageBtn, #saveDraftBtn, #saveCurrentBtn, #completeBtn').prop('disabled', false);
                
                if (response.success) {
                    // Mark stage as saved
                    savedStages.add(stage);
                    updateTabIndicators();
                    
                    // Update progress with completion percentage if provided
                    if (response.completion_percentage !== undefined) {
                        updateProgress(response.completion_percentage);
                    } else {
                        updateProgress(); // Fallback to calculated progress
                    }
                    
                    // Store user_id if this is first stage
                    if (response.user_id) {
                        $('#add_user_id').val(response.user_id);
                    }
                    
                    // Show success notification with completion percentage
                    const completionPercentage = response.completion_percentage !== undefined ? 
                        Math.round(response.completion_percentage) : null;
                    const completionMsg = completionPercentage !== null ? 
                        ` (${completionPercentage}% complete)` : '';
                    const message = isDraft ? 
                        `${stageName} saved as draft.${completionMsg}` : 
                        `${stageName} saved successfully!${completionMsg}`;
                    
                    // Enhanced toast notification
                    Swal.fire({
                        icon: 'success',
                        title: isDraft ? 'Draft Saved' : '✓ Saved Successfully',
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: isDraft ? 2500 : 3500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-border-success',
                            title: 'text-success fw-bold',
                            htmlContainer: 'text-dark'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            const borderColor = '#198754';
                            toast.style.borderLeft = `4px solid ${borderColor}`;
                        }
                    });
                    
                    // Show success message with option to continue
                    if (moveNext) {
                        // Get current stage index - use window variable if available
                        const currentIdx = typeof currentStageIndex !== 'undefined' ? currentStageIndex : window.currentStageIndex || 0;
                        const stageArray = typeof stages !== 'undefined' ? stages : window.stages || [];
                        
                        // Automatically move to next stage after save
                        const nextIndex = currentIdx + 1;
                        console.log('Moving to next stage. Current:', currentIdx, 'Next:', nextIndex, 'Total stages:', stageArray.length);
                        
                        if (nextIndex < stageArray.length) {
                                // Move to next stage - update both local and global variables
                                currentStageIndex = nextIndex;
                                window.currentStageIndex = nextIndex;
                                
                                const nextStage = stageArray[nextIndex];
                                console.log('Switching to next stage:', nextStage);
                                
                                $('#current_stage').val(nextStage);
                                
                                // Call update functions if they exist
                                if (typeof updateProgress === 'function') {
                                    updateProgress();
                                }
                                if (typeof updateButtons === 'function') {
                                    updateButtons();
                                }
                                if (typeof updateTabIndicators === 'function') {
                                    updateTabIndicators();
                                }
                                
                                // Show the next tab using Bootstrap tab API
                            const nextTabButton = $(`#${nextStage}-tab-btn`);
                                if (nextTabButton.length) {
                                    const tab = new bootstrap.Tab(nextTabButton[0]);
                                    tab.show();
                                
                                // Scroll to top of modal body
                                $('#addEmployeeModal .modal-body').scrollTop(0);
                                } else {
                                    console.error('Next tab button not found for stage:', nextStage);
                                }
                        } else {
                            // Last stage completed - show success toast then completion modal
                            Swal.fire({
                                icon: 'success',
                                title: '✓ All Stages Saved!',
                                text: 'All employee information has been saved successfully.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'swal2-toast-border-success',
                                    title: 'text-success fw-bold',
                                    htmlContainer: 'text-dark'
                                },
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                    const borderColor = '#198754';
                                    toast.style.borderLeft = `4px solid ${borderColor}`;
                                }
                            });
                            
                            // Show completion modal after toast
                            setTimeout(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: '🎉 Registration Complete!',
                                    html: '<p class="mb-3">All employee information has been saved successfully.</p><p class="text-muted">The employee can now access the system.</p>',
                                confirmButtonText: 'OK',
                                    confirmButtonColor: '#198754',
                                    width: '500px'
                            }).then(() => {
                                $('#addEmployeeModal').modal('hide');
                                location.reload();
                            });
                            }, 1000);
                        }
                    } else {
                        // Save without moving to next - just show success
                        // Already shown above
                    }
                } else {
                    // Response indicates failure - show toast notification
                    const errorMsg = response.message || 'An error occurred while saving. Please try again.';
                    Swal.fire({
                        icon: 'error',
                        title: '✗ Save Failed',
                        text: errorMsg,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-border-danger',
                            title: 'text-danger fw-bold',
                            htmlContainer: 'text-dark'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            const borderColor = '#dc3545';
                            toast.style.borderLeft = `4px solid ${borderColor}`;
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                clearTimeout(timeoutId);
                console.error('Save error:', {xhr, status, error});
                
                // Re-enable buttons
                saveBtn.prop('disabled', false).html(originalText);
                $('#prevStageBtn, #saveDraftBtn, #saveCurrentBtn, #completeBtn').prop('disabled', false);
                
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while saving.';
                let errorTitle = 'Save Failed';
                
                // Handle timeout - show toast notification
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. The server is taking too long to respond. Please try again.';
                    errorTitle = '✗ Request Timeout';
                    
                            Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-border-danger',
                            title: 'text-danger fw-bold',
                            htmlContainer: 'text-dark'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            const borderColor = '#dc3545';
                            toast.style.borderLeft = `4px solid ${borderColor}`;
                        }
                    });
                    return;
                }
                
                if (response) {
                    if (response.errors) {
                        // Validation errors
                        const errors = response.errors;
                        let errorList = '<ul class="text-start mb-0 mt-2">';
                        let errorCount = 0;
                        Object.keys(errors).forEach(field => {
                            const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                            messages.forEach(msg => {
                                errorList += `<li><strong>${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${msg}</li>`;
                                errorCount++;
                            });
                        });
                        errorList += '</ul>';
                        
                        errorTitle = `Validation Error${errorCount > 1 ? 's' : ''}`;
                        errorMessage = errorCount > 1 ? `Please fix ${errorCount} errors:` : errors[Object.keys(errors)[0]][0];
                    
                    // Show validation errors as toast with details
                    Swal.fire({
                        icon: 'error',
                        title: `✗ ${errorTitle}`,
                        html: `<div class="text-start"><p class="mb-2">${errorMessage}</p>${errorList}</div>`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: true,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33',
                        timer: 8000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-border-danger',
                            title: 'text-danger fw-bold',
                            htmlContainer: 'text-dark text-start'
                        },
                        width: '450px',
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            const borderColor = '#dc3545';
                            toast.style.borderLeft = `4px solid ${borderColor}`;
                        }
                        });
                        return;
                    } else if (response.message) {
                        errorMessage = response.message;
                    } else if (response.error) {
                        errorMessage = response.error;
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your internet connection.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please try again or contact support.';
                    if (response && response.error) {
                        errorMessage += ' Error: ' + response.error;
                    }
                } else if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to perform this action.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Resource not found. Please refresh the page.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation error. Please check your input.';
                    }
                    
                    // Show error as toast notification
                    Swal.fire({
                        icon: 'error',
                        title: `✗ ${errorTitle}`,
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-border-danger',
                            title: 'text-danger fw-bold',
                            htmlContainer: 'text-dark'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            const borderColor = '#dc3545';
                            toast.style.borderLeft = `4px solid ${borderColor}`;
                        }
                    });
            },
            complete: function(xhr, status) {
                // Always re-enable buttons when request completes
                clearTimeout(timeoutId);
                saveBtn.prop('disabled', false).html(originalText);
                $('#prevStageBtn, #saveDraftBtn, #saveCurrentBtn, #completeBtn').prop('disabled', false);
                console.log('Save request completed with status:', status);
            }
        });
        
        return true;
    }
    
    // Initialize progress on modal open
    // Handle position dropdown custom option in add modal
    $(document).on('change', '#add_position', function() {
        if ($(this).val() === '__custom__') {
            $('#add_position_custom').removeClass('d-none').focus();
        } else {
            $('#add_position_custom').addClass('d-none').val('');
        }
    });
    
    // Handle position custom input in add modal
    $(document).on('input', '#add_position_custom', function() {
        if ($(this).val().trim()) {
            $('#add_position').val('__custom__');
        }
    });
    
    // Handle position dropdown custom option in edit modal
    $(document).on('change', '#edit-position-select', function() {
        if ($(this).val() === '__custom__') {
            $('#edit-position-custom').removeClass('d-none').focus();
        } else {
            $('#edit-position-custom').addClass('d-none').val('');
        }
    });
    
    // Handle position custom input in edit modal
    $(document).on('input', '#edit-position-custom', function() {
        if ($(this).val().trim()) {
            $('#edit-position-select').val('__custom__');
        }
    });
    
    $('#addEmployeeModal').on('show.bs.modal', function() {
        currentStageIndex = 0;
        window.currentStageIndex = 0;
        savedStages.clear();
        updateProgress();
        updateButtons();
        updateTabIndicators();
    });
    
    @endif
    
    // Helper function to collect array form data with sequential indexing
    // This prevents database overflow issues with the 'order' column
    function collectFormArrayData(arrayName, formData) {
        const arrayData = {};
        const escapedArrayName = arrayName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const pattern = new RegExp(`^${escapedArrayName}\\[(\\d+)\\]\\[(.+)\\]$`);
        
        // Collect all data first
        $(`[name^="${arrayName}["]`).each(function() {
            const $input = $(this);
            const name = $input.attr('name');
            const type = $input.attr('type') || $input.prop('tagName').toLowerCase();
            
            if (!name) return;
            
            const match = name.match(pattern);
            if (!match) return;
            
            const index = match[1];
            const field = match[2];
            
            if (!arrayData[index]) {
                arrayData[index] = {};
            }
            
            if (type === 'checkbox') {
                arrayData[index][field] = $input.is(':checked') ? ($input.val() || '1') : '0';
            } else if (type === 'radio') {
                if ($input.is(':checked')) {
                    arrayData[index][field] = $input.val();
                }
            } else if (type === 'hidden') {
                arrayData[index][field] = $input.val() || '';
            } else {
                const value = $input.val();
                arrayData[index][field] = value !== null && value !== undefined ? (value || '') : '';
            }
        });
        
        // Re-index sequentially and append to formData
        if (Object.keys(arrayData).length > 0) {
            let sequentialIndex = 0;
            
            // Sort indices to maintain order (items with IDs first, then new items)
            const sortedIndices = Object.keys(arrayData).sort((a, b) => {
                const itemA = arrayData[a];
                const itemB = arrayData[b];
                const idA = itemA.id ? parseInt(itemA.id) : 999999999;
                const idB = itemB.id ? parseInt(itemB.id) : 999999999;
                return idA - idB;
            });
            
            sortedIndices.forEach(originalIndex => {
                const item = arrayData[originalIndex];
                const hasData = Object.keys(item).some(key => {
                    const value = item[key];
                    return value !== null && value !== undefined && value !== '';
                });
                
                if (hasData) {
                    Object.keys(item).forEach(field => {
                        const value = item[field];
                        // Use sequential index to prevent database overflow
                        formData.append(`${arrayName}[${sequentialIndex}][${field}]`, value !== null && value !== undefined ? (value || '') : '');
                    });
                    sequentialIndex++;
                }
            });
        }
    }
    
    // Functions for adding new items in add employee form
    function addFamilyMemberNew() {
        const idx = Date.now();
        const html = renderFamilyMemberAdd({}, idx);
        if ($('#addFamilyList').find('.text-muted').length > 0) {
            $('#addFamilyList').empty();
        }
        $('#addFamilyList').append(html);
    }
    
    function renderFamilyMemberAdd(member = {}, index = null) {
        const idx = index !== null ? index : Date.now();
        return `
            <div class="card mb-3 family-member-add" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Family Member</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFamilyMemberAdd(${idx})"><i class="bx bx-trash"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="family[${idx}][name]" placeholder="Name" value="${member.name || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="family[${idx}][relationship]" placeholder="Relationship" value="${member.relationship || ''}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="date" class="form-control" name="family[${idx}][date_of_birth]" value="${member.date_of_birth || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <select class="form-select" name="family[${idx}][gender]">
                                <option value="">Select Gender</option>
                                <option value="Male" ${member.gender === 'Male' ? 'selected' : ''}>Male</option>
                                <option value="Female" ${member.gender === 'Female' ? 'selected' : ''}>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="family[${idx}][occupation]" placeholder="Occupation" value="${member.occupation || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="family[${idx}][phone]" placeholder="Phone" value="${member.phone || ''}">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="family[${idx}][is_dependent]" value="1" ${member.is_dependent ? 'checked' : ''}>
                        <label class="form-check-label">Is Dependent</label>
                    </div>
                </div>
            </div>
        `;
    }
    
    function removeFamilyMemberAdd(index) {
        $(`.family-member-add[data-index="${index}"]`).remove();
        if ($('#addFamilyList').children().length === 0) {
            $('#addFamilyList').html('<p class="text-muted">No family members added yet.</p>');
        }
    }
    
    function addNextOfKinNew() {
        const idx = Date.now();
        const html = renderNextOfKinItemAdd({}, idx);
        if ($('#addNextOfKinList').find('.text-muted').length > 0) {
            $('#addNextOfKinList').empty();
        }
        $('#addNextOfKinList').append(html);
    }
    
    function renderNextOfKinItemAdd(kin = {}, index = null) {
        const idx = index !== null ? index : Date.now();
        return `
            <div class="card mb-3 next-of-kin-item-add" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Next of Kin</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeNextOfKinAdd(${idx})"><i class="bx bx-trash"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="next_of_kin[${idx}][name]" placeholder="Name" value="${kin.name || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="next_of_kin[${idx}][relationship]" placeholder="Relationship" value="${kin.relationship || ''}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="next_of_kin[${idx}][phone]" placeholder="Phone" value="${kin.phone || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="email" class="form-control" name="next_of_kin[${idx}][email]" placeholder="Email" value="${kin.email || ''}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="next_of_kin[${idx}][address]" placeholder="Address" rows="2" required>${kin.address || ''}</textarea>
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control" name="next_of_kin[${idx}][id_number]" placeholder="ID Number" value="${kin.id_number || ''}">
                    </div>
                </div>
            </div>
        `;
    }
    
    function removeNextOfKinAdd(index) {
        $(`.next-of-kin-item-add[data-index="${index}"]`).remove();
        if ($('#addNextOfKinList').children().length === 0) {
            $('#addNextOfKinList').html('<p class="text-muted">No next of kin added yet.</p>');
        }
    }
    
    function addRefereeNew() {
        const idx = Date.now();
        const html = renderRefereeItemAdd({}, idx);
        if ($('#addRefereesList').find('.text-muted').length > 0) {
            $('#addRefereesList').empty();
        }
        $('#addRefereesList').append(html);
    }
    
    function renderRefereeItemAdd(referee = {}, index = null) {
        const idx = index !== null ? index : Date.now();
        return `
            <div class="card mb-3 referee-item-add" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Referee</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRefereeAdd(${idx})"><i class="bx bx-trash"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="referees[${idx}][name]" placeholder="Name" value="${referee.name || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="referees[${idx}][position]" placeholder="Position" value="${referee.position || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="referees[${idx}][organization]" placeholder="Organization" value="${referee.organization || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="referees[${idx}][phone]" placeholder="Phone" value="${referee.phone || ''}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="email" class="form-control" name="referees[${idx}][email]" placeholder="Email" value="${referee.email || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="referees[${idx}][relationship]" placeholder="How they know the employee" value="${referee.relationship || ''}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="referees[${idx}][address]" placeholder="Address" rows="2">${referee.address || ''}</textarea>
                    </div>
                </div>
            </div>
        `;
    }
    
    function removeRefereeAdd(index) {
        $(`.referee-item-add[data-index="${index}"]`).remove();
        if ($('#addRefereesList').children().length === 0) {
            $('#addRefereesList').html('<p class="text-muted">No referees added yet.</p>');
        }
    }
    
    function addEducationNew() {
        const idx = Date.now();
        const html = renderEducationItemAdd({}, idx);
        if ($('#addEducationsList').find('.text-muted').length > 0) {
            $('#addEducationsList').empty();
        }
        $('#addEducationsList').append(html);
    }
    
    function renderEducationItemAdd(education = {}, index = null) {
        const idx = index !== null ? index : Date.now();
        const currentYear = new Date().getFullYear();
        let startYearOptions = '<option value="">Select</option>';
        let endYearOptions = '<option value="">Select</option>';
        for (let i = currentYear; i >= currentYear - 50; i--) {
            startYearOptions += `<option value="${i}" ${education.start_year == i ? 'selected' : ''}>${i}</option>`;
            endYearOptions += `<option value="${i}" ${education.end_year == i ? 'selected' : ''}>${i}</option>`;
        }
        
        return `
            <div class="card mb-3 education-item-add" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Education Record</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeEducationAdd(${idx})"><i class="bx bx-trash"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="educations[${idx}][institution_name]" placeholder="Institution Name" value="${education.institution_name || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="educations[${idx}][qualification]" placeholder="Qualification" value="${education.qualification || ''}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="educations[${idx}][field_of_study]" placeholder="Field of Study" value="${education.field_of_study || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="educations[${idx}][grade]" placeholder="Grade" value="${education.grade || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <select class="form-select" name="educations[${idx}][start_year]">${startYearOptions}</select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <select class="form-select" name="educations[${idx}][end_year]">${endYearOptions}</select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="educations[${idx}][description]" placeholder="Description" rows="2">${education.description || ''}</textarea>
                    </div>
                </div>
            </div>
        `;
    }
    
    function removeEducationAdd(index) {
        $(`.education-item-add[data-index="${index}"]`).remove();
        if ($('#addEducationsList').children().length === 0) {
            $('#addEducationsList').html('<p class="text-muted">No education records added yet.</p>');
        }
    }
    
    function addBankAccountNew() {
        const idx = Date.now();
        const html = renderBankAccountItemAdd({}, idx);
        if ($('#addBankAccountsList').find('.text-muted').length > 0) {
            $('#addBankAccountsList').empty();
        }
        $('#addBankAccountsList').append(html);
    }
    
    function renderBankAccountItemAdd(account = {}, index = null) {
        const idx = index !== null ? index : Date.now();
        const isPrimary = account.is_primary || $('#addBankAccountsList').children().length === 0;
        return `
            <div class="card mb-3 bank-account-item-add" data-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Bank Account</h6>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input primary-account-add" type="radio" name="primary_bank_account" value="${idx}" ${isPrimary ? 'checked' : ''} onchange="setPrimaryAccountAdd(${idx})">
                                <label class="form-check-label">Primary</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBankAccountAdd(${idx})"><i class="bx bx-trash"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="bank_accounts[${idx}][bank_name]" placeholder="Bank Name" value="${account.bank_name || ''}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="bank_accounts[${idx}][account_number]" placeholder="Account Number" value="${account.account_number || ''}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="bank_accounts[${idx}][account_name]" placeholder="Account Name" value="${account.account_name || ''}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="bank_accounts[${idx}][branch_name]" placeholder="Branch Name" value="${account.branch_name || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="bank_accounts[${idx}][swift_code]" placeholder="SWIFT Code" value="${account.swift_code || ''}">
                        </div>
                    </div>
                    <input type="hidden" name="bank_accounts[${idx}][is_primary]" value="${isPrimary ? '1' : '0'}" class="is-primary-input-add">
                </div>
            </div>
        `;
    }
    
    function removeBankAccountAdd(index) {
        $(`.bank-account-item-add[data-index="${index}"]`).remove();
        if ($('#addBankAccountsList').children().length === 0) {
            $('#addBankAccountsList').html('<p class="text-muted">No bank accounts added yet.</p>');
        } else {
            // Set first remaining account as primary if none selected
            const firstRadio = $('#addBankAccountsList').find('.primary-account-add').first();
            if (firstRadio.length && !$('#addBankAccountsList').find('.primary-account-add:checked').length) {
                firstRadio.prop('checked', true);
                setPrimaryAccountAdd(firstRadio.val());
            }
        }
    }
    
    function setPrimaryAccountAdd(index) {
        $('.is-primary-input-add').val('0');
        $(`.bank-account-item-add[data-index="${index}"] .is-primary-input-add`).val('1');
    }
    
    // Global function to open add employee modal
    window.openAddEmployeeModal = function() {
        $('#addEmployeeModal').data('action', 'create');
        $('#addEmployeeModalTitle').html('<i class="bx bx-user-plus me-2"></i>Add New Employee');
        $('#addEmployeeForm')[0].reset();
        $('#add_user_id').val('');
        currentStageIndex = 0;
        switchTab('personal');
        $('#personal-tab-btn').tab('show');
        updateProgress();
        updateButtons();
        $('#addEmployeeModal').modal('show');
    };
    
    // Upload photo form submission
    $('#uploadPhotoForm').submit(function(e) {
        e.preventDefault();
        
        const photoInput = $('#photo')[0];
        if (!photoInput || !photoInput.files || !photoInput.files[0]) {
            Swal.fire({
                icon: 'warning',
                title: 'No Photo Selected',
                text: 'Please select a photo to upload.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Validate file size (5MB max)
        const file = photoInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'The selected file is too large. Maximum size is 5MB.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Create FormData and explicitly append the photo file
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        const employeeId = $('#uploadPhotoModal').data('employee-id');
        
        if (!employeeId) {
            if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Employee ID is missing. Please refresh and try again.',
                confirmButtonText: 'OK'
            });
            } else {
                alert('Error: Employee ID is missing. Please refresh and try again.');
            }
            return;
        }
        
        // Check if Swal is available
        if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Upload Photo?',
            text: 'Are you sure you want to upload this photo?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Upload',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                uploadPhoto(employeeId, formData);
            }
        });
        } else {
            // Fallback if Swal is not available
            if (confirm('Are you sure you want to upload this photo?')) {
                uploadPhoto(employeeId, formData);
            }
        }
    });
    
    // Photo preview
    $('#photo').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
});

function viewEmployeeDetails(employeeId) {
    if (!employeeId) {
        Swal.fire('Error', 'Invalid employee ID.', 'error');
        return;
    }
    
    console.log('Loading employee details for ID:', employeeId);
    
    // Show loading state
    $('#employeeDetailsContent').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading employee details...</p></div>');
    $('#employeeDetailsModal').modal('show');
    
    // Load only essential data for viewing (faster) - use cache
    const startTime = Date.now();
    $.ajax({
        url: '{{ route("employees.show", ":id") }}'.replace(':id', employeeId),
        type: 'GET',
        data: {}, // Load basic info for viewing (no load_all flag)
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 15000, // Increase to 15 seconds
        cache: false, // Disable cache for debugging
        success: function(response) {
            console.log('Employee data received:', response);
            const loadTime = ((Date.now() - startTime) / 1000).toFixed(2);
            
            // Log performance info if available
            if (response._performance) {
                console.log(`Employee data loaded in ${loadTime}s (Query: ${response._performance.query_time_ms}ms, Cached: ${response._performance.cached ? 'Yes' : 'No'})`);
            } else {
                console.log(`Employee data loaded in ${loadTime}s`);
            }
            
            if (response.success && response.employee) {
                try {
                    // Update completion percentage
                    const completion = response.completion_percentage || 0;
                    updateViewCompletionPercentage(completion);
                    
                    displayEmployeeDetails(response.employee, response.canEdit, completion);
                } catch (e) {
                    console.error('Error displaying employee details:', e);
                    $('#employeeDetailsContent').html(`
                        <div class="alert alert-danger">
                            <h6><i class="bx bx-error-circle me-2"></i>Display Error</h6>
                            <p class="mb-2">Error rendering employee details. Please check the console.</p>
                            <small class="text-muted">${e.message || 'Unknown error'}</small>
                            <hr>
                            <button class="btn btn-sm btn-primary" onclick="viewEmployeeDetails(${employeeId})">
                                <i class="bx bx-refresh me-1"></i>Try Again
                            </button>
                        </div>
                    `);
                }
            } else {
                const errorMsg = response.message || 'Failed to load employee details.';
                console.error('Response error:', response);
                Swal.fire('Error', errorMsg, 'error');
                $('#employeeDetailsContent').html(`
                    <div class="alert alert-danger">
                        <h6><i class="bx bx-error-circle me-2"></i>Error</h6>
                        <p>${errorMsg}</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            const loadTime = ((Date.now() - startTime) / 1000).toFixed(2);
            console.error(`Error loading employee data after ${loadTime}s:`, error);
            console.error('XHR Status:', xhr.status);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'An error occurred while loading employee details.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Employee not found.';
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to view this employee.';
            } else if (status === 'timeout' || xhr.status === 0) {
                errorMessage = 'Request timed out. The server may be busy. Please try again.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error. Please check the console for details or contact administrator.';
            }
            
            // Clear loading state and show error
            $('#employeeDetailsContent').html(`
                <div class="alert alert-danger">
                    <h6><i class="bx bx-error-circle me-2"></i>Error Loading Employee Details</h6>
                    <p class="mb-2">${errorMessage}</p>
                    <small class="text-muted">Status: ${xhr.status || 'Unknown'} | Error: ${error || 'Unknown'}</small>
                    <hr>
                    <button class="btn btn-sm btn-primary" onclick="viewEmployeeDetails(${employeeId})">
                        <i class="bx bx-refresh me-1"></i>Try Again
                    </button>
                </div>
            `);
            
            Swal.fire({
                icon: 'error',
                title: 'Loading Error',
                text: errorMessage,
                footer: `Load time: ${loadTime}s | Status: ${xhr.status || 'Unknown'}`
            });
        }
    });
}

function updateViewCompletionPercentage(percentage) {
    const rounded = Math.round(percentage);
    $('#viewProgressBar').css('width', percentage + '%').attr('aria-valuenow', rounded);
    $('#viewProgressText').text(rounded + '%');
    $('#viewCompletionPercentage').text(rounded + '%');
    
    // Update progress bar color based on completion
    $('#viewProgressBar').removeClass('bg-primary bg-success bg-warning bg-danger');
    if (percentage >= 90) {
        $('#viewProgressBar').addClass('bg-success');
    } else if (percentage >= 70) {
        $('#viewProgressBar').addClass('bg-primary');
    } else if (percentage >= 50) {
        $('#viewProgressBar').addClass('bg-warning');
    } else {
        $('#viewProgressBar').addClass('bg-danger');
    }
}

function updateEditCompletionPercentage(percentage) {
    const rounded = Math.round(percentage);
    $('#editProgressBar').css('width', percentage + '%').attr('aria-valuenow', rounded);
    $('#editProgressText').text(rounded + '%');
    $('#editCompletionPercentage').text(rounded + '%');
    
    // Update status text
    let statusText = 'Incomplete';
    if (percentage >= 90) {
        statusText = 'Complete';
    } else if (percentage >= 70) {
        statusText = 'Almost Complete';
    } else if (percentage >= 50) {
        statusText = 'Partially Complete';
    }
    $('#editCompletionStatus').text(statusText);
    
    // Update progress bar color based on completion
    $('#editProgressBar').removeClass('bg-primary bg-success bg-warning bg-danger');
    if (percentage >= 90) {
        $('#editProgressBar').addClass('bg-success');
    } else if (percentage >= 70) {
        $('#editProgressBar').addClass('bg-primary');
    } else if (percentage >= 50) {
        $('#editProgressBar').addClass('bg-warning');
    } else {
        $('#editProgressBar').addClass('bg-danger');
    }
}

function displayEmployeeDetails(employee, canEdit, completionPercentage = 0) {
    if (!employee) {
        $('#employeeDetailsContent').html('<div class="alert alert-danger">No employee data available.</div>');
        return;
    }
    
    const firstName = employee.name ? employee.name.charAt(0) : 'U';
    const employeeId = employee.id || 0;
    const currentUserId = {{ Auth::id() }};
    const canChangePhoto = canEdit || employeeId == currentUserId;
    
    const html = `
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-4 ">
                    <div class="avatar avatar-xxl mb-3">
                        ${employee.photo ? 
                            `<img src="/storage/photos/${employee.photo}" alt="Employee Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">` :
                            `<span class="avatar-initial rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem;">${firstName}</span>`
                        }
                    </div>
                    <h5 class="mb-1">${employee.name || 'N/A'}</h5>
                    <p class="text-muted mb-0">${employee.employee_id || 'N/A'}</p>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="openUploadPhotoModal(${employeeId})">
                        <i class="bx bx-camera me-1"></i>${canChangePhoto ? 'Change Photo' : 'Upload Photo'}
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Email:</strong> ${employee.email}</p>
                                <p><strong>Phone:</strong> ${employee.phone || 'N/A'}</p>
                                <p><strong>Department:</strong> ${employee.primary_department ? employee.primary_department.name : 'N/A'}</p>
                                <p><strong>Hire Date:</strong> ${employee.hire_date ? new Date(employee.hire_date).toLocaleDateString() : 'N/A'}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge ${employee.is_active ? 'bg-label-success' : 'bg-label-danger'}">
                                        ${employee.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Employment Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Position:</strong> ${employee.employee ? employee.employee.position || 'N/A' : 'N/A'}</p>
                                <p><strong>Employment Type:</strong> ${employee.employee ? employee.employee.employment_type || 'N/A' : 'N/A'}</p>
                                <p><strong>Salary:</strong> ${employee.employee ? 'TZS ' + parseFloat(employee.employee.salary).toLocaleString() : 'N/A'}</p>
                                <p><strong>Roles:</strong> ${employee.roles ? employee.roles.map(role => role.display_name).join(', ') : 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Banking Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Bank:</strong> ${employee.employee ? employee.employee.bank_name || 'N/A' : 'N/A'}</p>
                                <p><strong>Account Number:</strong> ${employee.employee ? employee.employee.bank_account_number || 'N/A' : 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Tax & Statutory</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>TIN:</strong> ${employee.employee ? employee.employee.tin_number || 'N/A' : 'N/A'}</p>
                                <p><strong>NSSF:</strong> ${employee.employee ? employee.employee.nssf_number || 'N/A' : 'N/A'}</p>
                                <p><strong>NHIF:</strong> ${employee.employee ? employee.employee.nhif_number || 'N/A' : 'N/A'}</p>
                                <p><strong>HESLB:</strong> ${employee.employee ? employee.employee.heslb_number || 'N/A' : 'N/A'}</p>
                                <p><strong>Student Loan:</strong> ${employee.employee && employee.employee.has_student_loan ? 'Yes' : 'No'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add action buttons at the top
    const actionButtons = `
        <div class="mb-3 d-flex justify-content-end gap-2 flex-wrap">
            <a href="{{ route('employees.show', ':id') }}".replace(':id', ${employeeId}) class="btn btn-outline-primary" target="_blank">
                <i class="bx bx-show me-1"></i>View Full Details Page
            </a>
            <a href="{{ route('modules.hr.employees.registration-pdf', ':id') }}".replace(':id', ${employeeId}) class="btn btn-outline-danger" target="_blank">
                <i class="bx bx-file-blank me-1"></i>Generate PDF
            </a>
            <button class="btn btn-outline-success" onclick="sendEmployeeSMS(${employeeId})">
                <i class="bx bx-message me-1"></i>Send SMS
            </button>
        </div>
    `;
    
    $('#employeeDetailsContent').html(actionButtons + html);
    $('#editEmployeeBtn').toggle(canEdit);
    $('#editEmployeeBtn').off('click').on('click', function() {
        openEditEmployeeModal(employee.id);
    });
}

let currentEditEmployee = null;
let editEmployeeData = {};

function openEditEmployeeModal(employeeId) {
    if (!employeeId) {
        Swal.fire('Error', 'Invalid employee ID.', 'error');
        return;
    }
    
    $('#editEmployeeModal').data('employee-id', employeeId);
    $('#edit_employee_user_id').val(employeeId);
    
    // Show loading state
    $('#editTabContent').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading employee data...</p></div>');
    $('#editEmployeeModal').modal('show');
    
    // Load employee data with all relationships for editing
    $.ajax({
        url: '{{ route("employees.show", ":id") }}'.replace(':id', employeeId),
        type: 'GET',
        data: { load_all: 'true' },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        timeout: 30000, // 30 second timeout
        success: function(response) {
            console.log('Employee data loaded:', response);
            
            if (response.success && response.employee) {
                currentEditEmployee = employeeId;
                
                // Ensure employee data structure is consistent
                editEmployeeData = normalizeEmployeeData(response.employee);
                
                // Update completion percentage
                const completion = response.completion_percentage || 0;
                updateEditCompletionPercentage(completion);
                
                console.log('Normalized employee data:', editEmployeeData);
                console.log('Profile completion:', completion + '%');
                
                // Load personal info tab by default
                switchEditTab('personal');
                loadEditTabContent('personal', editEmployeeData);
            } else {
                const errorMsg = response.message || 'Failed to load employee data.';
                console.error('Failed to load employee:', errorMsg);
                $('#editTabContent').html(`
                    <div class="alert alert-danger">
                        <h6>Error Loading Employee Data</h6>
                        <p>${errorMsg}</p>
                        <button class="btn btn-sm btn-primary" onclick="openEditEmployeeModal(${employeeId})">
                            <i class="bx bx-refresh me-1"></i>Try Again
                        </button>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            let errorMessage = 'An error occurred while loading employee data.';
            
            if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Employee not found.';
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to view this employee.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error. Please contact administrator.';
            }
            
            $('#editTabContent').html(`
                <div class="alert alert-danger">
                    <h6>Error Loading Employee Data</h6>
                    <p>${errorMessage}</p>
                    <p class="small text-muted">Status: ${xhr.status || 'Unknown'} | Error: ${error || 'Unknown'}</p>
                    <button class="btn btn-sm btn-primary" onclick="openEditEmployeeModal(${employeeId})">
                        <i class="bx bx-refresh me-1"></i>Try Again
                    </button>
                </div>
            `);
        }
    });
}

// Normalize employee data structure to ensure consistency
function normalizeEmployeeData(employee) {
    if (!employee) {
        employee = {};
    }
    
    // Ensure employee.employee exists and preserve emergency contact data
    if (!employee.employee) {
        employee.employee = {
            id: null,
            user_id: employee.id || null,
            position: '',
            employment_type: 'permanent',
            salary: 0,
            tin_number: '',
            nssf_number: '',
            nhif_number: '',
            heslb_number: '',
            has_student_loan: false,
            emergency_contact_name: '',
            emergency_contact_phone: '',
            emergency_contact_relationship: '',
            emergency_contact_address: ''
        };
    } else {
        // Ensure emergency contact fields exist even if null
        employee.employee.emergency_contact_name = employee.employee.emergency_contact_name || '';
        employee.employee.emergency_contact_phone = employee.employee.emergency_contact_phone || '';
        employee.employee.emergency_contact_relationship = employee.employee.emergency_contact_relationship || '';
        employee.employee.emergency_contact_address = employee.employee.emergency_contact_address || '';
    }
    
    // Ensure arrays exist - handle both camelCase and snake_case
    if (!employee.family) employee.family = [];
    // Handle both nextOfKin (camelCase from backend) and next_of_kin (snake_case)
    if (!employee.nextOfKin && !employee.next_of_kin) {
        employee.nextOfKin = [];
        employee.next_of_kin = [];
    } else if (employee.nextOfKin && !employee.next_of_kin) {
        employee.next_of_kin = employee.nextOfKin;
    } else if (employee.next_of_kin && !employee.nextOfKin) {
        employee.nextOfKin = employee.next_of_kin;
    }
    if (!employee.referees) employee.referees = [];
    if (!employee.educations) employee.educations = [];
    // Handle both bankAccounts (camelCase from backend) and bank_accounts (snake_case)
    if (!employee.bankAccounts && !employee.bank_accounts) {
        employee.bankAccounts = [];
        employee.bank_accounts = [];
    } else if (employee.bankAccounts && !employee.bank_accounts) {
        employee.bank_accounts = employee.bankAccounts;
    } else if (employee.bank_accounts && !employee.bankAccounts) {
        employee.bankAccounts = employee.bank_accounts;
    }
    // Handle salary deductions
    if (!employee.salaryDeductions && !employee.deductions && !employee.salary_deductions) {
        employee.salaryDeductions = [];
        employee.deductions = [];
        employee.salary_deductions = [];
    } else if (employee.salaryDeductions && !employee.deductions) {
        employee.deductions = employee.salaryDeductions;
        employee.salary_deductions = employee.salaryDeductions;
    } else if (employee.deductions && !employee.salaryDeductions) {
        employee.salaryDeductions = employee.deductions;
        employee.salary_deductions = employee.deductions;
    }
    if (!employee.roles) employee.roles = [];
    // Handle documents array
    if (!employee.documents && !employee.employee_documents) {
        employee.documents = [];
        employee.employee_documents = [];
    } else if (employee.documents && !employee.employee_documents) {
        employee.employee_documents = employee.documents;
    } else if (employee.employee_documents && !employee.documents) {
        employee.documents = employee.employee_documents;
    }
    
    // Ensure primaryDepartment exists
    if (!employee.primaryDepartment) {
        employee.primaryDepartment = { id: null, name: '' };
    }
    
    // Ensure basic fields exist
    employee.name = employee.name || '';
    employee.email = employee.email || '';
    employee.phone = employee.phone || '';
    employee.employee_id = employee.employee_id || '';
    employee.hire_date = employee.hire_date || '';
    employee.is_active = employee.is_active !== undefined ? employee.is_active : true;
    employee.primary_department_id = employee.primary_department_id || employee.primaryDepartment?.id || null;
    
    // Preserve photo field and ensure photo_url is set
    employee.photo = employee.photo || '';
    if (employee.photo && !employee.photo_url) {
        // Build photo URL - try multiple possible paths
        // First try absolute URL with asset helper pattern
        const baseUrl = window.location.origin;
        employee.photo_url = `${baseUrl}/storage/photos/${employee.photo}`;
        // Also keep relative path for fallback
        employee.photo_path = `/storage/photos/${employee.photo}`;
    } else if (employee.photo_url) {
        // If photo_url exists, also create relative path
        employee.photo_path = employee.photo_url.replace(window.location.origin, '');
    }
    
    return employee;
}

function switchEditTab(section) {
    $('#edit_current_section').val(section);
    
    // Update active tab
    $('#editTabs .nav-link').removeClass('active');
    $(`#edit-${section}-tab`).addClass('active');
    
    // Load tab content
    loadEditTabContent(section, editEmployeeData);
}

function loadEditTabContent(section, employee) {
    let html = '';
    
    if (section === 'personal') {
        html = renderPersonalTab(employee);
    } else if (section === 'employment') {
        html = renderEmploymentTab(employee);
    } else if (section === 'emergency') {
        html = renderEmergencyTab(employee);
    } else if (section === 'family') {
        html = renderFamilyTab(employee);
    } else if (section === 'next-of-kin') {
        html = renderNextOfKinTab(employee);
    } else if (section === 'referees') {
        html = renderRefereesTab(employee);
    } else if (section === 'education') {
        html = renderEducationTab(employee);
    } else if (section === 'bank') {
        html = renderBankTab(employee);
    } else if (section === 'statutory') {
        html = renderStatutoryTab(employee);
    } else if (section === 'images') {
        html = renderImagesTab(employee);
    } else if (section === 'documents') {
        html = renderDocumentsTab(employee);
    }
    
    $('#editTabContent').html(html);
}

function renderPersonalTab(employee) {
    if (!employee) {
        employee = {};
    }
    const departments = @json($departments ?? []);
    let deptOptions = '<option value="">Select Department</option>';
    if (departments && Array.isArray(departments)) {
        departments.forEach(dept => {
            const selected = employee.primary_department_id == dept.id ? 'selected' : '';
            deptOptions += `<option value="${dept.id}" ${selected}>${dept.name || ''}</option>`;
        });
    }
    
    const hireDate = employee.hire_date ? (employee.hire_date.includes('T') ? employee.hire_date.split('T')[0] : employee.hire_date) : '';
    
    return `
        <div class="tab-pane fade show active" id="edit-personal" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="${employee.name || ''}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="${employee.email || ''}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone" value="${employee.phone || ''}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Hire Date</label>
                        <input type="date" class="form-control" name="hire_date" value="${hireDate}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" value="${employee.employee_id || 'Auto-generated on save'}" readonly disabled>
                        <small class="text-muted">Employee ID is auto-generated and cannot be edited</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-select" name="primary_department_id" required>${deptOptions}</select>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" ${employee.is_active ? 'checked' : ''}>
                    <label class="form-check-label">Active Employee</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation">
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderEmploymentTab(employee) {
    if (!employee) {
        employee = { employee: {}, roles: [] };
    }
    if (!employee.employee) {
        employee.employee = {};
    }
    if (!employee.roles) {
        employee.roles = [];
    }
    
    const roles = @json($roles ?? []);
    let rolesHtml = '';
    if (roles && Array.isArray(roles)) {
        roles.forEach(role => {
            const checked = employee.roles && employee.roles.some(r => r.id == role.id) ? 'checked' : '';
            rolesHtml += `
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="${role.id}" id="edit_role_${role.id}" ${checked}>
                        <label class="form-check-label" for="edit_role_${role.id}">${role.display_name || role.name || ''}</label>
                    </div>
                </div>
            `;
        });
    }
    
    const empType = employee.employee.employment_type || '';
    const salary = employee.employee.salary || '';
    
    return `
        <div class="tab-pane fade show active" id="edit-employment" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select class="form-select" name="position" id="edit-position-select">
                            <option value="">Select Position</option>
                            ${positionOptions}
                            <option value="__custom__">-- Enter Custom Position --</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="edit-position-custom" name="position_custom" placeholder="Enter custom position" value="${employee.employee.position || ''}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Employment Type</label>
                        <select class="form-select" name="employment_type">
                            <option value="permanent" ${empType === 'permanent' ? 'selected' : ''}>Permanent</option>
                            <option value="contract" ${empType === 'contract' ? 'selected' : ''}>Contract</option>
                            <option value="intern" ${empType === 'intern' ? 'selected' : ''}>Intern</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Basic Salary (TZS)</label>
                <input type="number" class="form-control" name="salary" step="0.01" min="0" value="${salary}">
            </div>
            <div class="mb-3">
                <label class="form-label">Roles</label>
                <div class="row">${rolesHtml || '<p class="text-muted">No roles available.</p>'}</div>
            </div>
        </div>
    `;
}

function renderEmergencyTab(employee) {
    if (!employee) employee = {};
    if (!employee.employee) employee.employee = {};
    
    // Log for debugging
    console.log('Rendering emergency tab, employee.employee:', employee.employee);
    
    const contactName = employee.employee.emergency_contact_name || '';
    const relationship = employee.employee.emergency_contact_relationship || '';
    const contactPhone = employee.employee.emergency_contact_phone || '';
    const contactAddress = employee.employee.emergency_contact_address || '';
    
    console.log('Emergency contact data:', {
        name: contactName,
        relationship: relationship,
        phone: contactPhone,
        address: contactAddress
    });
    
    return `
        <div class="tab-pane fade show active" id="edit-emergency" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" class="form-control" name="emergency_contact_name" value="${(contactName || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" placeholder="Enter contact name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Relationship</label>
                        <input type="text" class="form-control" name="emergency_contact_relationship" value="${(relationship || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" placeholder="e.g., Spouse, Parent, Sibling">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Emergency Phone</label>
                        <input type="text" class="form-control" name="emergency_contact_phone" value="${(contactPhone || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" placeholder="Enter phone number">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Emergency Address</label>
                <textarea class="form-control" name="emergency_contact_address" rows="3" placeholder="Enter full address">${(contactAddress || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}</textarea>
            </div>
        </div>
    `;
}

function renderFamilyTab(employee) {
    const family = employee.family || [];
    let familyHtml = '<div id="familyList">';
    if (family.length > 0) {
        family.forEach((member, index) => {
            familyHtml += renderFamilyMember(member, index);
        });
            } else {
        familyHtml += '<p class="text-muted">No family members added yet.</p>';
    }
    familyHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addFamilyMember()"><i class="bx bx-plus me-1"></i>Add Family Member</button>';
    return `<div class="tab-pane fade show active" id="edit-family" role="tabpanel">${familyHtml}</div>`;
}

function renderFamilyMember(member = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const memberId = member.id || '';
    const dateOfBirth = member.date_of_birth ? (member.date_of_birth.includes('T') ? member.date_of_birth.split('T')[0] : member.date_of_birth) : '';
    return `
        <div class="card mb-3 family-member" data-index="${idx}" data-id="${memberId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Family Member${memberId ? ' (ID: ' + memberId + ')' : ''}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFamilyMember(${idx})"><i class="bx bx-trash"></i></button>
                </div>
                ${memberId ? `<input type="hidden" name="family[${idx}][id]" value="${memberId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="family[${idx}][name]" placeholder="Name" value="${(member.name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="family[${idx}][relationship]" placeholder="Relationship" value="${(member.relationship || '').replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="date" class="form-control" name="family[${idx}][date_of_birth]" value="${dateOfBirth}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <select class="form-select" name="family[${idx}][gender]">
                            <option value="">Select Gender</option>
                            <option value="Male" ${member.gender === 'Male' ? 'selected' : ''}>Male</option>
                            <option value="Female" ${member.gender === 'Female' ? 'selected' : ''}>Female</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="family[${idx}][occupation]" placeholder="Occupation" value="${(member.occupation || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="family[${idx}][phone]" placeholder="Phone" value="${(member.phone || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="email" class="form-control" name="family[${idx}][email]" placeholder="Email" value="${(member.email || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="family[${idx}][address]" placeholder="Address" value="${(member.address || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="family[${idx}][is_dependent]" value="1" ${member.is_dependent ? 'checked' : ''}>
                    <label class="form-check-label">Is Dependent</label>
                </div>
            </div>
        </div>
    `;
}

function addFamilyMember() {
    const idx = Date.now();
    $('#familyList').append(renderFamilyMember({}, idx));
}

function removeFamilyMember(index) {
    $(`.family-member[data-index="${index}"]`).remove();
}

function renderNextOfKinTab(employee) {
    // Handle both camelCase and snake_case
    const nextOfKin = employee.nextOfKin || employee.next_of_kin || [];
    console.log('Rendering next of kin tab, count:', nextOfKin.length, 'data:', nextOfKin);
    let kinHtml = '<div id="nextOfKinList">';
    if (nextOfKin && nextOfKin.length > 0) {
        nextOfKin.forEach((kin, index) => {
            kinHtml += renderNextOfKinItem(kin, index);
        });
    } else {
        kinHtml += '<p class="text-muted">No next of kin added yet.</p>';
    }
    kinHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addNextOfKin()"><i class="bx bx-plus me-1"></i>Add Next of Kin</button>';
    return `<div class="tab-pane fade show active" id="edit-next-of-kin" role="tabpanel">${kinHtml}</div>`;
}

function renderNextOfKinItem(kin = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const kinId = kin.id || '';
    return `
        <div class="card mb-3 next-of-kin-item" data-index="${idx}" data-id="${kinId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Next of Kin${kinId ? ' (ID: ' + kinId + ')' : ''}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeNextOfKin(${idx})"><i class="bx bx-trash"></i></button>
                </div>
                ${kinId ? `<input type="hidden" name="next_of_kin[${idx}][id]" value="${kinId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="next_of_kin[${idx}][name]" placeholder="Name" value="${(kin.name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="next_of_kin[${idx}][relationship]" placeholder="Relationship" value="${(kin.relationship || '').replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="next_of_kin[${idx}][phone]" placeholder="Phone" value="${(kin.phone || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="email" class="form-control" name="next_of_kin[${idx}][email]" placeholder="Email" value="${(kin.email || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="mb-2">
                    <textarea class="form-control" name="next_of_kin[${idx}][address]" placeholder="Address" rows="2" required>${(kin.address || '').replace(/"/g, '&quot;')}</textarea>
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control" name="next_of_kin[${idx}][id_number]" placeholder="ID Number" value="${(kin.id_number || '').replace(/"/g, '&quot;')}">
                </div>
            </div>
        </div>
    `;
}

function addNextOfKin() {
    const idx = Date.now();
    $('#nextOfKinList').append(renderNextOfKinItem({}, idx));
}

function removeNextOfKin(index) {
    $(`.next-of-kin-item[data-index="${index}"]`).remove();
}

function renderRefereesTab(employee) {
    // Handle both camelCase and snake_case
    const referees = employee.referees || [];
    console.log('Rendering referees tab, referees count:', referees.length, 'referees:', referees);
    let refHtml = '<div id="refereesList">';
    if (referees && referees.length > 0) {
        referees.forEach((ref, index) => {
            refHtml += renderRefereeItem(ref, index);
        });
    } else {
        refHtml += '<p class="text-muted">No referees added yet.</p>';
    }
    refHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addReferee()"><i class="bx bx-plus me-1"></i>Add Referee</button>';
    return `<div class="tab-pane fade show active" id="edit-referees" role="tabpanel">${refHtml}</div>`;
}

function renderRefereeItem(referee = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const refId = referee.id || '';
    return `
        <div class="card mb-3 referee-item" data-index="${idx}" data-id="${refId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Referee${refId ? ' (ID: ' + refId + ')' : ''}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeReferee(${idx})"><i class="bx bx-trash"></i></button>
                </div>
                ${refId ? `<input type="hidden" name="referees[${idx}][id]" value="${refId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="referees[${idx}][name]" placeholder="Name" value="${(referee.name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="referees[${idx}][position]" placeholder="Position" value="${(referee.position || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="referees[${idx}][organization]" placeholder="Organization" value="${(referee.organization || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="referees[${idx}][phone]" placeholder="Phone" value="${(referee.phone || '').replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="email" class="form-control" name="referees[${idx}][email]" placeholder="Email" value="${(referee.email || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="referees[${idx}][relationship]" placeholder="How they know the employee" value="${(referee.relationship || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="mb-2">
                    <textarea class="form-control" name="referees[${idx}][address]" placeholder="Address" rows="2">${(referee.address || '').replace(/"/g, '&quot;')}</textarea>
                </div>
            </div>
        </div>
    `;
}

function addReferee() {
    const idx = Date.now();
    // Remove placeholder message if it exists
    if ($('#refereesList').find('.text-muted').length > 0) {
        $('#refereesList').empty();
    }
    $('#refereesList').append(renderRefereeItem({}, idx));
}

function removeReferee(index) {
    $(`.referee-item[data-index="${index}"]`).remove();
    // Show placeholder message if no referees remain
    if ($('#refereesList').children().length === 0) {
        $('#refereesList').html('<p class="text-muted">No referees added yet.</p>');
    }
}

function renderEducationTab(employee) {
    // Handle both camelCase and snake_case
    const educations = employee.educations || [];
    console.log('Rendering education tab, educations count:', educations.length, 'educations:', educations);
    let eduHtml = '<div id="educationsList">';
    if (educations && educations.length > 0) {
        educations.forEach((edu, index) => {
            eduHtml += renderEducationItem(edu, index);
        });
    } else {
        eduHtml += '<p class="text-muted">No education records added yet.</p>';
    }
    eduHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addEducation()"><i class="bx bx-plus me-1"></i>Add Education</button>';
    return `<div class="tab-pane fade show active" id="edit-education" role="tabpanel">${eduHtml}</div>`;
}

function renderEducationItem(education = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const eduId = education.id || '';
    const currentYear = new Date().getFullYear();
    let startYearOptions = '<option value="">Select</option>';
    let endYearOptions = '<option value="">Select</option>';
    for (let i = currentYear; i >= currentYear - 50; i--) {
        startYearOptions += `<option value="${i}" ${education.start_year == i ? 'selected' : ''}>${i}</option>`;
        endYearOptions += `<option value="${i}" ${education.end_year == i ? 'selected' : ''}>${i}</option>`;
    }
    
    return `
        <div class="card mb-3 education-item" data-index="${idx}" data-id="${eduId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Education Record${eduId ? ' (ID: ' + eduId + ')' : ''}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEducation(${idx})"><i class="bx bx-trash"></i></button>
                </div>
                ${eduId ? `<input type="hidden" name="educations[${idx}][id]" value="${eduId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="educations[${idx}][institution_name]" placeholder="Institution Name" value="${(education.institution_name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="educations[${idx}][qualification]" placeholder="Qualification" value="${(education.qualification || '').replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="educations[${idx}][field_of_study]" placeholder="Field of Study" value="${(education.field_of_study || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="educations[${idx}][grade]" placeholder="Grade" value="${(education.grade || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <select class="form-select" name="educations[${idx}][start_year]">${startYearOptions}</select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <select class="form-select" name="educations[${idx}][end_year]">${endYearOptions}</select>
                    </div>
                </div>
                <div class="mb-2">
                    <textarea class="form-control" name="educations[${idx}][description]" placeholder="Description" rows="2">${(education.description || '').replace(/"/g, '&quot;')}</textarea>
                </div>
            </div>
        </div>
    `;
}

function addEducation() {
    const idx = Date.now();
    // Remove placeholder message if it exists
    if ($('#educationsList').find('.text-muted').length > 0) {
        $('#educationsList').empty();
    }
    $('#educationsList').append(renderEducationItem({}, idx));
}

function removeEducation(index) {
    $(`.education-item[data-index="${index}"]`).remove();
    // Show placeholder message if no educations remain
    if ($('#educationsList').children().length === 0) {
        $('#educationsList').html('<p class="text-muted">No education records added yet.</p>');
    }
}

function renderBankTab(employee) {
    // Handle both camelCase and snake_case
    const accounts = employee.bankAccounts || employee.bank_accounts || [];
    console.log('Rendering bank tab, accounts count:', accounts.length, 'accounts:', accounts);
    let bankHtml = '<div id="bankAccountsList">';
    if (accounts && accounts.length > 0) {
        accounts.forEach((account, index) => {
            bankHtml += renderBankAccountItem(account, index);
        });
    } else {
        bankHtml += '<p class="text-muted">No bank accounts added yet.</p>';
    }
    bankHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addBankAccount()"><i class="bx bx-plus me-1"></i>Add Bank Account</button>';
    return `<div class="tab-pane fade show active" id="edit-bank" role="tabpanel">${bankHtml}</div>`;
}

function renderBankAccountItem(account = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const accountId = account.id || '';
    return `
        <div class="card mb-3 bank-account-item" data-index="${idx}" data-id="${accountId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Bank Account${accountId ? ' (ID: ' + accountId + ')' : ''}</h6>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input primary-account" type="radio" name="primary_bank_account" value="${idx}" ${account.is_primary ? 'checked' : ''} onchange="setPrimaryAccount(${idx})">
                            <label class="form-check-label">Primary</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeBankAccount(${idx})"><i class="bx bx-trash"></i></button>
                    </div>
                </div>
                ${accountId ? `<input type="hidden" name="bank_accounts[${idx}][id]" value="${accountId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="bank_accounts[${idx}][bank_name]" placeholder="Bank Name" value="${(account.bank_name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="bank_accounts[${idx}][account_number]" placeholder="Account Number" value="${(account.account_number || '').replace(/"/g, '&quot;')}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="bank_accounts[${idx}][account_name]" placeholder="Account Name" value="${(account.account_name || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="bank_accounts[${idx}][branch_name]" placeholder="Branch Name" value="${(account.branch_name || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="bank_accounts[${idx}][swift_code]" placeholder="SWIFT Code" value="${(account.swift_code || '').replace(/"/g, '&quot;')}">
                    </div>
                </div>
                <input type="hidden" name="bank_accounts[${idx}][is_primary]" value="${account.is_primary ? '1' : '0'}" class="is-primary-input">
            </div>
        </div>
    `;
}

function addBankAccount() {
    const idx = Date.now();
    $('#bankAccountsList').append(renderBankAccountItem({}, idx));
}

function removeBankAccount(index) {
    $(`.bank-account-item[data-index="${index}"]`).remove();
}

function setPrimaryAccount(index) {
    $('.is-primary-input').val('0');
    $(`.bank-account-item[data-index="${index}"] .is-primary-input`).val('1');
}

function renderStatutoryTab(employee) {
    if (!employee) employee = {};
    if (!employee.employee) employee.employee = {};
    
    // Load deductions if available - handle multiple possible property names
    const deductions = employee.salaryDeductions || employee.deductions || employee.salary_deductions || [];
    console.log('Rendering statutory tab, deductions count:', deductions.length, 'deductions:', deductions);
    let deductionsHtml = '<div id="deductionsList">';
    if (deductions && deductions.length > 0) {
        deductions.forEach((ded, index) => {
            deductionsHtml += renderDeductionItem(ded, index);
        });
    } else {
        deductionsHtml += '<p class="text-muted">No deductions added yet.</p>';
    }
    deductionsHtml += '</div><button type="button" class="btn btn-sm btn-primary mt-2" onclick="addDeduction()"><i class="bx bx-plus me-1"></i>Add Deduction</button>';
    
    return `
        <div class="tab-pane fade show active" id="edit-statutory" role="tabpanel">
            <h6 class="mb-3">Statutory Information</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">TIN Number</label>
                    <input type="text" class="form-control" name="tin_number" value="${employee.employee?.tin_number || ''}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NSSF Number</label>
                    <input type="text" class="form-control" name="nssf_number" value="${employee.employee?.nssf_number || ''}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NHIF Number</label>
                    <input type="text" class="form-control" name="nhif_number" value="${employee.employee?.nhif_number || ''}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">HESLB Number</label>
                    <input type="text" class="form-control" name="heslb_number" value="${employee.employee?.heslb_number || ''}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="has_student_loan" value="1" ${employee.employee?.has_student_loan ? 'checked' : ''}>
                    <label class="form-check-label">Has Student Loan (HESLB)</label>
                </div>
            </div>
            
            <hr class="my-4">
            <h6 class="mb-3">Salary Deductions (Loans, Advances, etc.)</h6>
            ${deductionsHtml}
        </div>
    `;
}

function renderDeductionItem(deduction = {}, index = null) {
    const idx = index !== null ? index : Date.now();
    const dedId = deduction.id || '';
    const startDate = deduction.start_date ? (deduction.start_date.includes('T') ? deduction.start_date.split('T')[0] : deduction.start_date) : '';
    const endDate = deduction.end_date ? (deduction.end_date.includes('T') ? deduction.end_date.split('T')[0] : deduction.end_date) : '';
    
    return `
        <div class="card mb-3 deduction-item" data-index="${idx}" data-id="${dedId}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Deduction${dedId ? ' (ID: ' + dedId + ')' : ''}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeDeduction(${idx})"><i class="bx bx-trash"></i></button>
                </div>
                ${dedId ? `<input type="hidden" name="deductions[${idx}][id]" value="${dedId}">` : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="deductions[${idx}][deduction_type]" required>
                            <option value="loan" ${deduction.deduction_type === 'loan' ? 'selected' : ''}>Loan</option>
                            <option value="advance" ${deduction.deduction_type === 'advance' ? 'selected' : ''}>Salary Advance</option>
                            <option value="insurance" ${deduction.deduction_type === 'insurance' ? 'selected' : ''}>Insurance</option>
                            <option value="other" ${(!deduction.deduction_type || deduction.deduction_type === 'other') ? 'selected' : ''}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="deductions[${idx}][amount]" step="0.01" min="0" value="${deduction.amount || ''}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="deductions[${idx}][frequency]">
                            <option value="monthly" ${(!deduction.frequency || deduction.frequency === 'monthly') ? 'selected' : ''}>Monthly</option>
                            <option value="one-time" ${deduction.frequency === 'one-time' ? 'selected' : ''}>One-time</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control deduction-start-date" name="deductions[${idx}][start_date]" value="${startDate}" required data-index="${idx}" onchange="validateDeductionDates(${idx})">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">End Date (optional)</label>
                        <input type="date" class="form-control deduction-end-date" name="deductions[${idx}][end_date]" value="${endDate}" data-index="${idx}" onchange="validateDeductionDates(${idx})">
                        <small class="text-muted">Leave blank for ongoing deduction</small>
                        <div class="invalid-feedback deduction-date-error" style="display: none;">End date must be after start date</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="deductions[${idx}][is_active]" value="1" ${deduction.is_active !== false ? 'checked' : ''}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="deductions[${idx}][description]" placeholder="Brief description" value="${(deduction.description || '').replace(/"/g, '&quot;')}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="deductions[${idx}][notes]" placeholder="Additional notes" rows="2">${(deduction.notes || '').replace(/"/g, '&quot;')}</textarea>
                </div>
            </div>
        </div>
    `;
}

function addDeduction() {
    const idx = Date.now();
    $('#deductionsList').append(renderDeductionItem({}, idx));
}

function removeDeduction(index) {
    $(`.deduction-item[data-index="${index}"]`).remove();
}

// Validate deduction date range
function validateDeductionDates(index) {
    const deductionItem = $(`.deduction-item[data-index="${index}"]`);
    const startDateInput = deductionItem.find('.deduction-start-date');
    const endDateInput = deductionItem.find('.deduction-end-date');
    const errorDiv = deductionItem.find('.deduction-date-error');
    
    const startDate = startDateInput.val();
    const endDate = endDateInput.val();
    
    // Clear previous error state
    startDateInput.removeClass('is-invalid');
    endDateInput.removeClass('is-invalid');
    errorDiv.hide();
    
    // Validate only if both dates are provided
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (start > end) {
            // Show error
            endDateInput.addClass('is-invalid');
            errorDiv.show();
            return false;
        }
    }
    
    return true;
}

// Validate all deductions before saving
function validateAllDeductions() {
    let isValid = true;
    $('.deduction-item').each(function() {
        const index = $(this).data('index');
        if (!validateDeductionDates(index)) {
            isValid = false;
        }
    });
    return isValid;
}

function renderImagesTab(employee) {
    if (!employee) employee = {};
    const employeeId = employee.id || currentEditEmployee || 0;
    const firstName = employee.name ? employee.name.charAt(0) : 'U';
    
    // Build photo URL - try multiple possible sources
    let photoUrl = '';
    let photoUrlFallback = '';
    if (employee.photo && employee.photo.trim() !== '') {
        // Try photo_url first (absolute URL from backend)
        if (employee.photo_url) {
            photoUrl = employee.photo_url;
            // Extract filename from URL if it's a full URL
            const filename = employee.photo;
            photoUrlFallback = `/storage/photos/${filename}`;
        } else if (employee.photo_path) {
            // Use relative path if available
            photoUrl = employee.photo_path;
            photoUrlFallback = `/storage/photos/${employee.photo}`;
        } else {
            // Build from photo field - use route helper pattern
            const filename = employee.photo;
            photoUrl = `/storage/photos/${filename}`;
            // Also try absolute URL as fallback
            const baseUrl = window.location.origin;
            photoUrlFallback = `${baseUrl}/storage/photos/${filename}`;
        }
    }
    
    // Check if photo exists (not empty string)
    const hasPhoto = !!(employee.photo && employee.photo.trim() !== '' && employee.photo !== 'null');
    
    console.log('Rendering images tab, employee:', employee);
    console.log('Photo exists:', hasPhoto, 'photo field:', employee.photo, 'photoUrl:', photoUrl, 'fallback:', photoUrlFallback);
    
    return `
        <div class="tab-pane fade show active" id="edit-images" role="tabpanel">
            <div class="text-center">
                <div class="mb-3 position-relative" style="display: inline-block;">
                    ${hasPhoto ? 
                        `<img src="${photoUrl}?t=${Date.now()}" alt="Profile Picture" class="rounded-circle" style="width: 200px; height: 200px; object-fit: cover; border: 3px solid #198754; display: block;" id="profilePhotoDisplay" 
                             onerror="console.error('Photo load error with primary URL:', this.src); 
                                      if ('${photoUrlFallback}' && this.src !== '${photoUrlFallback}?t=' + Date.now()) {
                                          console.log('Trying fallback URL:', '${photoUrlFallback}');
                                          this.src = '${photoUrlFallback}?t=' + Date.now();
                                          return;
                                      }
                                      console.error('Both photo URLs failed, showing fallback avatar');
                                      this.style.display='none'; 
                                      const fallback = this.nextElementSibling; 
                                      if (fallback) {
                                          fallback.style.display='flex';
                                      } else {
                                          const newFallback = document.createElement('div');
                                          newFallback.className = 'rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center';
                                          newFallback.style.cssText = 'width: 200px; height: 200px; font-size: 4rem; display: flex;';
                                          newFallback.textContent = '${firstName}';
                                          this.parentNode.appendChild(newFallback);
                                      }">
                        <div class="rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center" style="width: 200px; height: 200px; font-size: 4rem; display: none; position: absolute; top: 0; left: 0;">${firstName}</div>` :
                        `<div class="rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center" style="width: 200px; height: 200px; font-size: 4rem; display: flex;">${firstName}</div>`
                    }
                </div>
                <p class="text-muted mb-3">${hasPhoto ? 'Current profile picture' : 'No profile picture uploaded'}</p>
                <button type="button" class="btn btn-primary" onclick="openUploadPhotoModal(${employeeId})">
                    <i class="bx bx-camera me-2"></i>${hasPhoto ? 'Change' : 'Upload'} Profile Picture
                </button>
            </div>
        </div>
    `;
}

function renderDocumentsTab(employee) {
    if (!employee) employee = {};
    const employeeId = employee.id || currentEditEmployee || 0;
    const documents = employee.documents || employee.employee_documents || [];
    
    console.log('Rendering documents tab, employee:', employee, 'documents:', documents);
    
    // Document types for dropdown
    const documentTypes = [
        'Contract',
        'ID Copy',
        'Certificate',
        'CV/Resume',
        'Diploma',
        'License',
        'Passport',
        'Medical Report',
        'Training Certificate',
        'Performance Review',
        'Disciplinary Action',
        'Other'
    ];
    
    let documentsHtml = '';
    if (documents && documents.length > 0) {
        // Simple list view with basic details
        documents.forEach((doc, index) => {
            const fileSize = doc.file_size ? formatFileSize(doc.file_size) : 'N/A';
            const fileIcon = getFileIcon(doc.file_type || doc.file_name || '');
            const issueDate = doc.issue_date ? new Date(doc.issue_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
            const expiryDate = doc.expiry_date ? new Date(doc.expiry_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : null;
            const uploadDate = doc.created_at ? new Date(doc.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
            const isExpired = doc.expiry_date && new Date(doc.expiry_date) < new Date();
            const isExpiringSoon = doc.expiry_date && !isExpired && new Date(doc.expiry_date) <= new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
            const daysUntilExpiry = doc.expiry_date ? Math.ceil((new Date(doc.expiry_date) - new Date()) / (1000 * 60 * 60 * 24)) : null;
            
            documentsHtml += `
                <div class="card mb-2 document-item border document-item-clickable" data-document-id="${doc.id}" data-document-type="${doc.document_type || ''}" onclick="viewDocumentDetails(${doc.id})" style="cursor: pointer;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-${getFileColor(doc.file_type || '')} d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="${fileIcon}" style="font-size: 1.5rem;"></i>
                                    </span>
                                </div>
                            </div>
                                    <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">${doc.document_name || doc.file_name || 'Document'}</h6>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-label-primary">
                                                <i class="bx bx-tag me-1"></i>${doc.document_type || 'Other'}
                                            </span>
                                    <span class="text-muted small">
                                                <i class="bx bx-file me-1"></i>${fileSize}
                                            </span>
                                    ${expiryDate ? `<span class="text-muted small">
                                        <i class="bx bx-calendar me-1"></i>Exp: ${expiryDate}
                                    </span>` : ''}
                                    ${isExpired ? '<span class="badge bg-danger"><i class="bx bx-time-five me-1"></i>Expired</span>' : ''}
                                    ${isExpiringSoon ? `<span class="badge bg-warning"><i class="bx bx-time me-1"></i>Expires in ${daysUntilExpiry} days</span>` : ''}
                                    ${!isExpired && !isExpiringSoon && expiryDate ? '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Active</span>' : ''}
                                        </div>
                                    </div>
                            <div class="flex-shrink-0">
                                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); viewDocumentDetails(${doc.id})">
                                    <i class="bx bx-info-circle me-1"></i>View Details
                                        </button>
                                    </div>
                                </div>
                                            </div>
                                        </div>
            `;
        });
        
        // Add "Add More Documents" section
        documentsHtml += `
            <div class="card border-primary border-dashed mt-3">
                <div class="card-body text-center p-4">
                    <i class="bx bx-plus-circle text-primary mb-2" style="font-size: 3rem;"></i>
                    <h6 class="mb-2">Add More Documents</h6>
                    <p class="text-muted small mb-3">Upload additional documents for this employee</p>
                    <button type="button" class="btn btn-primary" onclick="openUploadDocumentModal(${employeeId})">
                        <i class="bx bx-upload me-2"></i>Upload More Documents
                    </button>
                    </div>
                </div>
            `;
    } else {
        documentsHtml = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bx bx-folder-open text-muted" style="font-size: 5rem; opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted mb-2">No Documents Found</h5>
                <p class="text-muted mb-4">Upload documents to organize and manage employee files</p>
                <button type="button" class="btn btn-primary" onclick="openUploadDocumentModal(${employeeId})">
                    <i class="bx bx-upload me-2"></i>Upload First Document
                </button>
            </div>
        `;
    }
    
    let typeOptions = '<option value="">Select Document Type</option>';
    documentTypes.forEach(type => {
        typeOptions += `<option value="${type}">${type}</option>`;
    });
    
    return `
        <div class="tab-pane fade show active" id="edit-documents" role="tabpanel">
            <!-- Search and Filter Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="documentSearch" placeholder="Search documents..." onkeyup="filterDocuments()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="documentTypeFilter" onchange="filterDocuments()">
                                <option value="">All Types</option>
                                ${documentTypes.map(type => `<option value="${type}">${type}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="documentStatusFilter" onchange="filterDocuments()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="expiring_soon">Expiring Soon</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-primary" onclick="openUploadDocumentModal(${employeeId})">
                                <i class="bx bx-upload me-1"></i>${documents && documents.length > 0 ? 'Add More' : 'Upload'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents List -->
            <div id="documentsList">
                ${documentsHtml}
            </div>
            
            <!-- Upload Document Modal -->
            <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold">
                                <i class="bx bx-cloud-upload me-2"></i>Upload Employee Documents
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="uploadDocumentForm" enctype="multipart/form-data">
                            <div class="modal-body p-4">
                                <div class="row">
                                    <!-- Left Column: File Upload Area (60% width) -->
                                    <div class="col-lg-7 mb-4 mb-lg-0">
                                        <h6 class="mb-3 fw-semibold">
                                            <i class="bx bx-file me-2 text-primary"></i>Select Files
                                        </h6>
                                        
                                        <!-- Enhanced Drag and Drop Area -->
                                        <div class="border-3 border-dashed rounded-3 p-5 text-center mb-4" id="documentDropZone" 
                                             style="border-color: #696cff; background: linear-gradient(135deg, #f8f9ff 0%, #f0f0ff 100%); cursor: pointer; transition: all 0.3s ease; min-height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center;"
                                             ondrop="handleDocumentDrop(event)" 
                                             ondragover="handleDocumentDragOver(event)" 
                                             ondragleave="handleDocumentDragLeave(event)"
                                             onclick="document.getElementById('documentFileInput').click()">
                                            <div class="mb-4">
                                                <i class="bx bx-cloud-upload text-primary" style="font-size: 4rem;"></i>
                                            </div>
                                            <h5 class="mb-2 fw-semibold">Drag & Drop Files Here</h5>
                                            <p class="text-muted mb-3">or click to browse from your computer</p>
                                            <button type="button" class="btn btn-primary btn-lg">
                                                <i class="bx bx-folder-open me-2"></i>Browse Files
                                            </button>
                                            <div class="mt-4 pt-3 border-top">
                                                <p class="text-muted small mb-2">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    <strong>Supported formats:</strong> PDF, DOC, DOCX, JPG, PNG, XLS, XLSX
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    <strong>Maximum file size:</strong> 10MB per file
                                                </p>
                                            </div>
                                        </div>
                                        <input type="file" id="documentFileInput" class="d-none" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx" onchange="handleDocumentFileSelect(event)">
                                        
                                        <!-- Enhanced Selected Files Preview -->
                                        <div id="selectedFilesPreview" class="mb-0">
                                            <h6 class="mb-3 fw-semibold">
                                                <i class="bx bx-list-ul me-2 text-primary"></i>Selected Files
                                                <span class="badge bg-primary ms-2" id="fileCountBadge" style="display: none;">0</span>
                                            </h6>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Column: Document Details (40% width) -->
                                    <div class="col-lg-5">
                                        <h6 class="mb-3 fw-semibold">
                                            <i class="bx bx-info-circle me-2 text-primary"></i>Document Information
                                        </h6>
                                        
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">
                                                        Document Type <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select form-select-lg" name="document_type" id="document_type" required>
                                                        ${typeOptions}
                                                    </select>
                                                    <small class="text-muted">Select the category this document belongs to</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">
                                                        Document Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control form-control-lg" name="document_name" id="document_name" placeholder="Enter a descriptive name" required>
                                                    <small class="text-muted">This name will be displayed in the documents list</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-semibold">
                                                    <i class="bx bx-calendar me-2"></i>Date Information
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Issue Date</label>
                                                    <input type="date" class="form-control" name="issue_date" id="issue_date">
                                                    <small class="text-muted">When was this document issued?</small>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Expiry Date</label>
                                                    <input type="date" class="form-control" name="expiry_date" id="expiry_date">
                                                    <small class="text-muted">When does this document expire? (if applicable)</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-semibold">
                                                    <i class="bx bx-building me-2"></i>Issuing Authority
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Issued By</label>
                                                    <input type="text" class="form-control" name="issued_by" id="issued_by" placeholder="Organization/Authority name">
                                                    <small class="text-muted">Who issued this document?</small>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Document Number</label>
                                                    <input type="text" class="form-control" name="document_number" id="document_number" placeholder="Reference number or ID">
                                                    <small class="text-muted">Official document reference number</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-semibold">
                                                    <i class="bx bx-note me-2"></i>Additional Notes
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Description</label>
                                                    <textarea class="form-control" name="description" id="document_description" rows="4" placeholder="Add any additional information, notes, or context about this document..."></textarea>
                                                    <small class="text-muted">Optional: Provide context or important details</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-top">
                                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bx bx-upload me-2"></i>Upload Documents
                                    <span class="badge bg-white text-primary ms-2" id="uploadFileCount" style="display: none;">0</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Document Preview Modal -->
            <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="documentPreviewTitle">Document Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="documentPreviewContent">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="downloadPreviewBtn" onclick="downloadDocument(null)">
                                <i class="bx bx-download me-2"></i>Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper functions for documents
function formatFileSize(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function getFileIcon(fileType) {
    const ext = fileType.toLowerCase();
    if (ext.includes('pdf')) return 'bx bxs-file-pdf';
    if (ext.includes('doc')) return 'bx bxs-file-doc';
    if (ext.includes('xls') || ext.includes('excel')) return 'bx bxs-file';
    if (ext.includes('jpg') || ext.includes('jpeg') || ext.includes('png') || ext.includes('image')) return 'bx bxs-image';
    return 'bx bxs-file';
}

function getFileColor(fileType) {
    const ext = fileType.toLowerCase();
    if (ext.includes('pdf')) return 'danger';
    if (ext.includes('doc')) return 'primary';
    if (ext.includes('xls') || ext.includes('excel')) return 'success';
    if (ext.includes('jpg') || ext.includes('jpeg') || ext.includes('png') || ext.includes('image')) return 'info';
    return 'secondary';
}

function filterDocuments() {
    const searchTerm = $('#documentSearch').val().toLowerCase();
    const typeFilter = $('#documentTypeFilter').val();
    const statusFilter = $('#documentStatusFilter').val();
    
    $('.document-item').each(function() {
        const $item = $(this);
        const text = $item.text().toLowerCase();
        const docType = $item.find('.badge').first().text();
        const hasExpired = $item.find('.badge.bg-danger').length > 0;
        const hasExpiringSoon = $item.find('.badge.bg-warning').length > 0;
        
        let show = true;
        
        if (searchTerm && !text.includes(searchTerm)) {
            show = false;
        }
        
        if (typeFilter && docType !== typeFilter) {
            show = false;
        }
        
        if (statusFilter === 'expired' && !hasExpired) {
            show = false;
        } else if (statusFilter === 'expiring_soon' && !hasExpiringSoon) {
            show = false;
        } else if (statusFilter === 'active' && (hasExpired || hasExpiringSoon)) {
            show = false;
        }
        
        $item.toggle(show);
    });
}

let selectedDocumentFiles = [];
let currentPreviewDocumentId = null;

function handleDocumentDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    $('#documentDropZone').css({
        'border-color': '#696cff', 
        'background': 'linear-gradient(135deg, #e8eaff 0%, #d6d9ff 100%)',
        'transform': 'scale(1.02)'
    });
}

function handleDocumentDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    $('#documentDropZone').css({
        'border-color': '#696cff', 
        'background': 'linear-gradient(135deg, #f8f9ff 0%, #f0f0ff 100%)',
        'transform': 'scale(1)'
    });
}

function handleDocumentDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    $('#documentDropZone').css({
        'border-color': '#696cff', 
        'background': 'linear-gradient(135deg, #f8f9ff 0%, #f0f0ff 100%)',
        'transform': 'scale(1)'
    });
    
    const files = Array.from(e.dataTransfer.files);
    processSelectedFiles(files);
}

function handleDocumentFileSelect(e) {
    const files = Array.from(e.target.files);
    processSelectedFiles(files);
}

function processSelectedFiles(files) {
    const preview = $('#selectedFilesPreview');
    const existingCount = selectedDocumentFiles.length;
    
    files.forEach((file, index) => {
        if (file.size > 10 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: file.name + ' is larger than 10MB. Please select a smaller file.',
                    toast: true,
                    position: 'top-end',
                    timer: 3000
                });
            }
            return;
        }
        
        const fileIndex = existingCount + index;
        selectedDocumentFiles.push(file);
        const fileSize = formatFileSize(file.size);
        const fileIconClass = getFileIcon(file.name);
        const fileColor = getFileColor(file.name);
        
        preview.append(`
            <div class="card mb-2 border shadow-sm" data-file-index="${fileIndex}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-${fileColor}">
                                <i class="${fileIconClass}"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">${file.name}</h6>
                            <div class="d-flex align-items-center gap-3">
                                <small class="text-muted">
                                    <i class="bx bx-file me-1"></i>${fileSize}
                                </small>
                                <small class="text-muted">
                                    <i class="bx bx-code-alt me-1"></i>${file.type || 'Unknown'}
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="removeSelectedFile(${fileIndex})" title="Remove file">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
        
        // Auto-fill document name if empty
        if (!$('#document_name').val()) {
            $('#document_name').val(file.name.replace(/\.[^/.]+$/, ""));
        }
    });
    
    // Update file count badges
    updateFileCountBadges();
}

function removeSelectedFile(index) {
    selectedDocumentFiles.splice(index, 1);
    $(`[data-file-index="${index}"]`).remove();
    
    // Re-index remaining files
    $('#selectedFilesPreview .card').each(function(i) {
        $(this).attr('data-file-index', i);
        $(this).find('button').attr('onclick', 'removeSelectedFile(' + i + ')');
    });
    
    // Update file count badges
    updateFileCountBadges();
}

function updateFileCountBadges() {
    const count = selectedDocumentFiles.length;
    $('#fileCountBadge').text(count).toggle(count > 0);
    $('#uploadFileCount').text(count).toggle(count > 0);
}

function openUploadDocumentModal(employeeId) {
    $('#uploadDocumentModal').data('employee-id', employeeId);
    $('#uploadDocumentForm')[0].reset();
    selectedDocumentFiles = [];
    $('#selectedFilesPreview').empty();
    updateFileCountBadges();
    $('#uploadDocumentModal').modal('show');
}

function viewDocumentDetails(documentId) {
    const employeeId = $('#edit_employee_user_id').val();
    
    if (!documentId || !employeeId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Document ID or Employee ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    // Fetch document details
    $.ajax({
        url: `/employees/${employeeId}/documents/${documentId}`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success && response.document) {
                const doc = response.document;
                const fileSize = doc.file_size ? formatFileSize(doc.file_size) : 'N/A';
                const fileIcon = getFileIcon(doc.file_type || doc.file_name || '');
                const issueDate = doc.issue_date ? new Date(doc.issue_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
                const expiryDate = doc.expiry_date ? new Date(doc.expiry_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : null;
                const uploadDate = doc.created_at ? new Date(doc.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
                const updatedDate = doc.updated_at ? new Date(doc.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
                const isExpired = doc.expiry_date && new Date(doc.expiry_date) < new Date();
                const isExpiringSoon = doc.expiry_date && !isExpired && new Date(doc.expiry_date) <= new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                const uploadedBy = doc.uploader ? doc.uploader.name : (doc.uploaded_by_name || 'System');
                const daysUntilExpiry = doc.expiry_date ? Math.ceil((new Date(doc.expiry_date) - new Date()) / (1000 * 60 * 60 * 24)) : null;
                
                let detailsHtml = `
                    <div class="row g-3">
                        <div class="col-12 text-center mb-3">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-lg bg-label-${getFileColor(doc.file_type || '')} d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="${fileIcon}" style="font-size: 3rem;"></i>
                                </span>
                            </div>
                            <h4 class="mb-2">${doc.document_name || doc.file_name || 'Document'}</h4>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <span class="badge bg-label-primary">
                                    <i class="bx bx-tag me-1"></i>${doc.document_type || 'Other'}
                                </span>
                                <span class="badge bg-label-secondary">
                                    <i class="bx bx-file me-1"></i>${fileSize}
                                </span>
                                <span class="badge bg-label-info">
                                    <i class="bx bx-code-alt me-1"></i>${(doc.file_type || 'unknown').toUpperCase()}
                                </span>
                                ${isExpired ? '<span class="badge bg-danger"><i class="bx bx-time-five me-1"></i>Expired</span>' : ''}
                                ${isExpiringSoon ? `<span class="badge bg-warning"><i class="bx bx-time me-1"></i>Expires in ${daysUntilExpiry} days</span>` : ''}
                                ${!isExpired && !isExpiringSoon && expiryDate ? '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Active</span>' : ''}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="bx bx-file-blank me-2"></i>File Information</h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Original Filename:</th>
                                            <td>${doc.file_name || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>File Size:</th>
                                            <td>${fileSize}</td>
                                        </tr>
                                        <tr>
                                            <th>File Type:</th>
                                            <td>${(doc.file_type || 'unknown').toUpperCase()}</td>
                                        </tr>
                                        <tr>
                                            <th>Uploaded By:</th>
                                            <td>${uploadedBy}</td>
                                        </tr>
                                        <tr>
                                            <th>Uploaded On:</th>
                                            <td>${uploadDate}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>${updatedDate}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="bx bx-info-circle me-2"></i>Document Details</h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Document Type:</th>
                                            <td>${doc.document_type || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Document Name:</th>
                                            <td>${doc.document_name || 'N/A'}</td>
                                        </tr>
                                        ${doc.document_number ? `<tr>
                                            <th>Document Number:</th>
                                            <td>${doc.document_number}</td>
                                        </tr>` : ''}
                                        <tr>
                                            <th>Issue Date:</th>
                                            <td>${issueDate}</td>
                                        </tr>
                                        ${expiryDate ? `<tr>
                                            <th>Expiry Date:</th>
                                            <td class="${isExpired ? 'text-danger' : isExpiringSoon ? 'text-warning' : ''}">
                                                ${expiryDate}
                                                ${daysUntilExpiry !== null ? `<br><small class="${isExpired ? 'text-danger' : isExpiringSoon ? 'text-warning' : 'text-muted'}">${isExpired ? 'Expired' : daysUntilExpiry + ' days remaining'}</small>` : ''}
                                            </td>
                                        </tr>` : '<tr><th>Expiry Date:</th><td>N/A</td></tr>'}
                                        ${doc.issued_by ? `<tr>
                                            <th>Issued By:</th>
                                            <td>${doc.issued_by}</td>
                                        </tr>` : ''}
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                ${doc.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        ${doc.description ? `<div class="col-12">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                                    <p class="mb-0">${doc.description}</p>
                                </div>
                            </div>
                        </div>` : ''}
                        
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-primary" onclick="previewDocument(${doc.id}, '${doc.file_url || ''}')">
                                    <i class="bx bx-show me-2"></i>Preview
                                </button>
                                <button class="btn btn-outline-primary" onclick="downloadDocument(${doc.id})">
                                    <i class="bx bx-download me-2"></i>Download
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteDocument(${doc.id}, '${doc.document_name || doc.file_name || ''}')">
                                    <i class="bx bx-trash me-2"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
            title: 'Document Details',
                        html: detailsHtml,
                        width: '800px',
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'text-start'
                        }
                    });
                } else {
                    // Fallback modal
                    const modal = `
                        <div class="modal fade show" id="documentDetailsModal" style="display: block; z-index: 9999;" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Document Details</h5>
                                        <button type="button" class="btn-close" onclick="$('#documentDetailsModal').remove(); $('.modal-backdrop').remove();"></button>
                                    </div>
                                    <div class="modal-body">
                                        ${detailsHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-backdrop fade show"></div>
                    `;
                    $('body').append(modal);
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Document not found.',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
    }
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to load document details.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    toast: true,
                    position: 'top-end',
                    timer: 3000
                });
            }
        }
    });
}

function previewDocument(documentId, fileUrl) {
    currentPreviewDocumentId = documentId;
    $('#documentPreviewModal').modal('show');
    $('#documentPreviewContent').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
    
    // Try to load the document
    if (fileUrl) {
        const ext = fileUrl.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            $('#documentPreviewContent').html(`<img src="${fileUrl}" class="img-fluid" alt="Document Preview">`);
        } else if (ext === 'pdf') {
            $('#documentPreviewContent').html(`<iframe src="${fileUrl}" style="width: 100%; height: 600px; border: none;"></iframe>`);
        } else {
            $('#documentPreviewContent').html(`
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <p>Preview not available for this file type. Please download to view.</p>
                    <button class="btn btn-primary" onclick="downloadDocument(${documentId})">
                        <i class="bx bx-download me-2"></i>Download Document
                    </button>
                </div>
            `);
        }
    }
}

function downloadDocument(documentId) {
    if (!documentId) {
        documentId = currentPreviewDocumentId;
    }
    
    if (!documentId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Document ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    const employeeId = $('#edit_employee_user_id').val();
    window.open(`/employees/${employeeId}/documents/${documentId}/download`, '_blank');
}

function deleteDocument(documentId, documentName) {
    if (!documentId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Document ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    const employeeId = $('#edit_employee_user_id').val();
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Delete Document?',
            text: 'Are you sure you want to delete "' + documentName + '"? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/employees/${employeeId}/documents/${documentId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Document has been deleted successfully.',
                                toast: true,
                                position: 'top-end',
                                timer: 3000
                            }).then(() => {
                                // Reload documents tab
                                loadEditTabContent('documents', editEmployeeData);
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: response?.message || 'An error occurred while deleting the document.',
                            toast: true,
                            position: 'top-end',
                            timer: 4000
                        });
                    }
                });
            }
        });
    }
}

// Handle document upload form submission
$(document).on('submit', '#uploadDocumentForm', function(e) {
    e.preventDefault();
    
    if (selectedDocumentFiles.length === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'No Files Selected',
                text: 'Please select at least one file to upload.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    const employeeId = $('#uploadDocumentModal').data('employee-id');
    const formData = new FormData();
    
    // Append all files
    selectedDocumentFiles.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    
    // Append form data
    formData.append('document_type', $('#document_type').val());
    formData.append('document_name', $('#document_name').val());
    formData.append('issue_date', $('#issue_date').val() || '');
    formData.append('expiry_date', $('#expiry_date').val() || '');
    formData.append('issued_by', $('#issued_by').val() || '');
    formData.append('document_number', $('#document_number').val() || '');
    formData.append('description', $('#document_description').val() || '');
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');
    
    $.ajax({
        url: `/employees/${employeeId}/documents`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    submitBtn.html(`<span class="spinner-border spinner-border-sm me-2"></span>Uploading ${Math.round(percentComplete)}%`);
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '✓ Uploaded!',
                        text: (response.count || selectedDocumentFiles.length) + ' document(s) uploaded successfully.',
                        toast: true,
                        position: 'top-end',
                        timer: 3000
                    });
                }
                
                $('#uploadDocumentModal').modal('hide');
                
                // Reload documents tab
                if (typeof loadEditTabContent === 'function') {
                    loadEditTabContent('documents', editEmployeeData);
                } else {
                    location.reload();
                }
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            const response = xhr.responseJSON;
            const errorMsg = response?.message || 'An error occurred while uploading documents.';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: errorMsg,
                    toast: true,
                    position: 'top-end',
                    timer: 4000
                });
            }
        }
    });
});

function saveCurrentSection() {
    const section = $('#edit_current_section').val();
    const employeeId = $('#edit_employee_user_id').val();
    
    console.log('Saving section:', section, 'for employee:', employeeId);
    
    if (!employeeId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Employee ID is missing.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Validate deductions if saving statutory section
    if (section === 'statutory') {
        if (!validateAllDeductions()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fix the date errors in deductions. End date must be after start date.',
                confirmButtonText: 'OK'
            });
            return;
        }
    }
    
    const formData = new FormData();
    const tabPane = $(`#edit-${section}`);
    
    // Show loading state on the correct button
    const saveBtn = $('#saveCurrentSectionBtn');
    const originalText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    
    // Also disable other buttons during save
    $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', true);
    
    // Show status text
    $('#saveStatusText').html('<span class="text-info"><i class="bx bx-loader-alt bx-spin me-1"></i>Saving...</span>');
    
    // Collect data based on section type
    if (section === 'family') {
        collectArrayData('family', tabPane, formData);
    } else if (section === 'next-of-kin') {
        collectArrayData('next_of_kin', tabPane, formData);
    } else if (section === 'referees') {
        collectArrayData('referees', tabPane, formData);
    } else if (section === 'education') {
        collectArrayData('educations', tabPane, formData);
    } else if (section === 'statutory') {
        // Handle regular form fields for statutory
        tabPane.find('input, select, textarea').each(function() {
            const $input = $(this);
            const name = $input.attr('name');
            const type = $input.attr('type');
            
            if (!name) return;
            
            // Skip deductions array fields - handled separately
            if (name.includes('deductions[') && name.includes(']')) return;
            
            if (type === 'checkbox') {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val() || '1');
                } else {
                    formData.append(name, '0');
                }
            } else {
                const value = $input.val();
                if (value !== null && value !== undefined) {
                    formData.append(name, value || '');
                }
            }
        });
        // Collect deductions array data
        collectArrayData('deductions', tabPane, formData);
    } else if (section === 'bank') {
        collectArrayData('bank_accounts', tabPane, formData);
        // Handle primary bank account selection
        const primaryAccount = tabPane.find('input[name="primary_bank_account"]:checked').val();
        if (primaryAccount) {
            formData.append('primary_bank_account', primaryAccount);
        }
    } else {
        // Handle regular form fields for other sections
        tabPane.find('input, select, textarea').each(function() {
            const $input = $(this);
            const name = $input.attr('name');
            const type = $input.attr('type');
            
            if (!name) return;
            
            // Handle roles array separately (it's a special case)
            if (name === 'roles[]') {
                if ($input.is(':checked')) {
                    formData.append('roles[]', $input.val());
                }
                return;
            }
            
            // Skip other array fields - they're handled above
            if (name.includes('[') && name.includes(']') && name !== 'roles[]') return;
            
            if (type === 'checkbox') {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val() || '1');
                } else {
                    formData.append(name, '0');
                }
            } else if (type === 'radio') {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val());
                }
            } else if (type === 'hidden') {
                formData.append(name, $input.val());
            } else {
                const value = $input.val();
                // Always append, even if empty (for emergency contact and other fields)
                // This ensures empty fields are sent to clear them in the database
                if (value !== null && value !== undefined) {
                    formData.append(name, value || '');
                } else {
                    formData.append(name, '');
                }
            }
        });
    }
    
    // Ensure roles array is always sent (even if empty) for employment section
    if (section === 'employment') {
        // Check if any roles were selected
        const rolesSelected = tabPane.find('input[name="roles[]"]:checked').length > 0;
        if (!rolesSelected) {
            // Send empty array to clear all roles
            formData.append('roles', '[]');
        }
        // Note: Selected roles are already appended above in the loop
    }
    
    formData.append('section', section);
    formData.append('_method', 'PUT');
    
    // Debug: Log form data being sent
    console.log('Form data for section:', section);
    const formDataEntries = [];
    for (let pair of formData.entries()) {
        formDataEntries.push(pair[0] + ': ' + pair[1]);
    }
    console.log('Form data entries:', formDataEntries);
    
    // Set timeout for the request
    const timeoutId = setTimeout(function() {
        if (saveBtn.prop('disabled')) {
            // Request is taking too long
            saveBtn.prop('disabled', false).html(originalText);
            $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
            $('#saveStatusText').html('<span class="text-warning"><i class="bx bx-time-five me-1"></i>Request taking longer than expected...</span>');
        }
    }, 30000); // 30 seconds warning
    
    $.ajax({
        url: `/employees/${employeeId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        timeout: 60000, // 60 second timeout
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function() {
            console.log('Saving section:', section, 'for employee:', employeeId);
        },
        success: function(response) {
            clearTimeout(timeoutId);
            console.log('Save response received:', response);
            
            // Validate response structure
            if (!response || typeof response !== 'object') {
                console.error('Invalid response format:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Response',
                    text: 'The server returned an invalid response. Please try again.',
                    confirmButtonText: 'OK'
                });
            saveBtn.prop('disabled', false).html(originalText);
                $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
                $('#saveStatusText').html('<span class="text-danger"><i class="bx bx-error-circle me-1"></i>Invalid response</span>');
                return;
            }
            
            // Re-enable all buttons
            saveBtn.prop('disabled', false).html(originalText);
            $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
            $('#saveStatusText').text('');
            
            if (response.success) {
                // Update the employee data with fresh data from server - reload all relationships
                if (response.employee) {
                    // Reload employee data with all relationships to get fresh data
                    $.ajax({
                        url: `/employees/${currentEditEmployee}`,
                        type: 'GET',
                        data: { load_all: 'true' },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(refreshResponse) {
                            if (refreshResponse.success && refreshResponse.employee) {
                                editEmployeeData = normalizeEmployeeData(refreshResponse.employee);
                    // Reload current tab content to show updated data
                                loadEditTabContent(section, editEmployeeData);
                                console.log('Data refreshed after save, section:', section, 'data:', editEmployeeData);
                            }
                        },
                        error: function() {
                            // Fallback to using response data
                            editEmployeeData = normalizeEmployeeData(response.employee);
                            loadEditTabContent(section, editEmployeeData);
                        }
                    });
                } else {
                    // Fallback if no employee data in response
                    editEmployeeData = normalizeEmployeeData(response.employee || {});
                    loadEditTabContent(section, editEmployeeData);
                }
                
                // Update completion percentage if provided
                if (response.completion_percentage !== undefined) {
                    updateEditCompletionPercentage(response.completion_percentage);
                }
                
                // Show success notification with toast-style SweetAlert
                const sectionNames = {
                    'personal': 'Personal Information',
                    'employment': 'Employment Information',
                    'emergency': 'Emergency Contact',
                    'family': 'Family Information',
                    'next-of-kin': 'Next of Kin',
                    'referees': 'Referees',
                    'education': 'Education',
                    'bank': 'Bank Details',
                    'statutory': 'Deductions',
                    'images': 'Profile Image'
                };
                
                const sectionName = sectionNames[section] || section.charAt(0).toUpperCase() + section.slice(1);
                const message = response.message || `${sectionName} saved successfully.`;
                const completionMsg = response.completion_percentage !== undefined ? 
                    ` (Profile: ${Math.round(response.completion_percentage)}% complete)` : '';
                
                // Update status text
                $('#saveStatusText').html(`<span class="text-success"><i class="bx bx-check-circle me-1"></i>Saved successfully${completionMsg}</span>`);
                setTimeout(() => $('#saveStatusText').text(''), 3000);
                
                // Show advanced toast notification
                Swal.fire({
                    icon: 'success',
                    title: '✓ Saved Successfully!',
                    html: `
                        <div class="text-start">
                            <p class="mb-1"><strong>${sectionName}</strong> has been saved to the database.</p>
                            ${completionMsg ? `<p class="mb-0 text-primary"><i class="bx bx-tachometer me-1"></i>${completionMsg}</p>` : ''}
                        </div>
                    `,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#f8f9fa',
                    customClass: {
                        popup: 'swal2-toast-border-success',
                        title: 'text-success fw-bold',
                        htmlContainer: 'text-dark'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                        // Add border color
                        toast.style.borderLeft = '4px solid #198754';
                    }
                });
            } else {
                // Show advanced error alert
                let errorDetails = response.message || 'An error occurred while saving.';
                if (response.errors) {
                    const errorList = Object.keys(response.errors).map(key => 
                        `<li><strong>${key.replace(/_/g, ' ')}:</strong> ${Array.isArray(response.errors[key]) ? response.errors[key].join(', ') : response.errors[key]}</li>`
                    ).join('');
                    errorDetails = `<div class="text-start"><p class="mb-2">${errorDetails}</p><ul class="mb-0">${errorList}</ul></div>`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: '✗ Save Failed',
                    html: errorDetails,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33',
                    customClass: {
                        popup: 'swal2-error-popup',
                        title: 'text-danger fw-bold'
                    },
                    width: '500px'
                });
                
                $('#saveStatusText').html('<span class="text-danger"><i class="bx bx-error-circle me-1"></i>Save failed</span>');
            }
        },
        error: function(xhr, status, error) {
            clearTimeout(timeoutId);
            console.error('Save error:', {xhr, status, error});
            console.error('Response status:', xhr.status);
            console.error('Response text:', xhr.responseText);
            console.error('Response JSON:', xhr.responseJSON);
            
            // Re-enable all buttons immediately
            saveBtn.prop('disabled', false).html(originalText);
            $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
            
            const response = xhr.responseJSON;
            let errorMessage = 'An error occurred while saving.';
            let errorDetails = null;
            let errorTitle = '✗ Save Failed';
            
            // Handle timeout specifically
            if (status === 'timeout' || xhr.status === 0) {
                errorMessage = 'Request timed out or connection lost. The server is taking too long to respond. Please check your connection and try again.';
                errorTitle = '✗ Request Timeout';
                
                Swal.fire({
                    icon: 'error',
                    title: errorTitle,
                    html: `<div class="text-start"><p class="mb-0"><i class="bx bx-time-five me-2"></i>${errorMessage}</p></div>`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33',
                    customClass: {
                        popup: 'swal2-error-popup',
                        title: 'text-danger fw-bold',
                        htmlContainer: 'text-dark'
                    },
                    width: '450px'
                });
                
                $('#saveStatusText').html('<span class="text-danger"><i class="bx bx-time-five me-1"></i>Request timed out</span>');
                return;
            }
            
            if (response) {
                if (response.errors) {
                    // Validation errors
                    const errors = response.errors;
                    let errorList = '<ul class="text-start mb-0 mt-2">';
                    let errorCount = 0;
                    Object.keys(errors).forEach(field => {
                        const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                        messages.forEach(msg => {
                            errorList += `<li><strong>${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${msg}</li>`;
                            errorCount++;
                        });
                    });
                    errorList += '</ul>';
                    
                    errorTitle = `Validation Error${errorCount > 1 ? 's' : ''}`;
                    errorMessage = errorCount > 1 ? `Please fix ${errorCount} errors:` : errors[Object.keys(errors)[0]][0];
                    
                    Swal.fire({
                        icon: 'error',
                        title: `✗ ${errorTitle}`,
                        html: `<div class="text-start"><p class="mb-2">${errorMessage}</p>${errorList}</div>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33',
                        customClass: {
                            popup: 'swal2-error-popup',
                            title: 'text-danger fw-bold',
                            htmlContainer: 'text-dark'
                        },
                        width: '500px'
                    });
                    
                    $('#saveStatusText').html(`<span class="text-danger"><i class="bx bx-error-circle me-1"></i>Validation failed</span>`);
                    return;
                } else if (response.message) {
                    errorMessage = response.message;
                } else if (response.error) {
                    errorMessage = response.error;
                }
            } else if (xhr.status === 0) {
                errorMessage = 'Network error. Please check your internet connection.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again or contact support.';
                if (response && response.error) {
                    errorMessage += ' Error: ' + response.error;
                }
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to perform this action.';
            } else if (xhr.status === 404) {
                errorMessage = 'Employee not found. Please refresh the page.';
            } else if (xhr.status === 422) {
                errorMessage = 'Validation error. Please check your input.';
            } else if (xhr.status >= 400 && xhr.status < 500) {
                errorMessage = 'Client error. Please check your request.';
            } else if (xhr.status >= 500) {
                errorMessage = 'Server error. Please try again later.';
            }
            
            // Check if Swal is available before using it
            if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: errorTitle,
                text: errorMessage,
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
            } else {
                // Fallback to alert if Swal is not available
                alert(errorTitle + ': ' + errorMessage);
            }
            
            $('#saveStatusText').html(`<span class="text-danger"><i class="bx bx-error-circle me-1"></i>Error: ${errorMessage.substring(0, 30)}...</span>`);
        },
        complete: function(xhr, status) {
            // Always re-enable buttons when request completes (success or error)
            clearTimeout(timeoutId);
            saveBtn.prop('disabled', false).html(originalText);
            $('#saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
            
            console.log('Save request completed with status:', status);
        }
    });
}

// Helper function to collect array data for sections with multiple items
function collectArrayData(arrayName, container, formData) {
    const arrayData = {};
    
    console.log('Collecting array data for:', arrayName, 'container:', container);
    
    // Collect all fields for this array - use a more flexible regex pattern
    // Escape special regex characters in arrayName
    const escapedArrayName = arrayName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    // Match pattern: arrayName[index][field] where index can be any number
    const pattern = new RegExp(`^${escapedArrayName}\\[(\\d+)\\]\\[(.+)\\]$`);
    
    console.log('Pattern for', arrayName, ':', pattern);
    
    // Find all inputs that start with the array name
    const inputs = container.find(`input[name^="${arrayName}["], select[name^="${arrayName}["], textarea[name^="${arrayName}["]`);
    console.log('Found', inputs.length, 'inputs for', arrayName);
    
    inputs.each(function() {
        const $input = $(this);
        const name = $input.attr('name');
        const type = $input.attr('type') || $input.prop('tagName').toLowerCase();
        
        if (!name) {
            console.warn('Input has no name attribute');
            return;
        }
        
        // Parse array name and index: e.g., "referees[123][name]" -> index: 123, field: name
        const match = name.match(pattern);
        if (!match) {
            console.warn('No match for name:', name, 'arrayName:', arrayName);
            return;
        }
        
        const index = match[1];
        const field = match[2];
        
        if (!arrayData[index]) {
            arrayData[index] = {};
        }
        
        if (type === 'checkbox') {
            arrayData[index][field] = $input.is(':checked') ? ($input.val() || '1') : '0';
        } else if (type === 'radio') {
            if ($input.is(':checked')) {
                arrayData[index][field] = $input.val();
            }
        } else if (type === 'hidden') {
            arrayData[index][field] = $input.val() || '';
        } else {
            const value = $input.val();
            // Always store the value, even if empty (for validation purposes)
            arrayData[index][field] = value !== null && value !== undefined ? (value || '') : '';
        }
        
        console.log('Collected:', arrayName, '[', index, '][', field, '] =', arrayData[index][field]);
    });
    
    console.log('Collected array data:', arrayData);
    
    // Always append array data to formData for all items that have any data
    // The backend will validate required fields
    // Re-index array with sequential indices (0, 1, 2...) to avoid database overflow issues
    if (Object.keys(arrayData).length > 0) {
        let appendedCount = 0;
        let sequentialIndex = 0;
        
        // Sort indices to maintain order, then re-index sequentially
        const sortedIndices = Object.keys(arrayData).sort((a, b) => {
            // Keep original order for items with IDs, new items go to the end
            const itemA = arrayData[a];
            const itemB = arrayData[b];
            const idA = itemA.id ? parseInt(itemA.id) : 999999999;
            const idB = itemB.id ? parseInt(itemB.id) : 999999999;
            return idA - idB;
        });
        
        sortedIndices.forEach(originalIndex => {
            const item = arrayData[originalIndex];
            // Check if item has any data (at least one field with a value)
            const hasData = Object.keys(item).some(key => {
                const value = item[key];
                return value !== null && value !== undefined && value !== '';
            });
            
            // Send item if it has any data - let backend validate required fields
            if (hasData) {
                Object.keys(item).forEach(field => {
                    const value = item[field];
                    // Use sequential index instead of original timestamp index
                    // This prevents database overflow issues with 'order' column
                    formData.append(`${arrayName}[${sequentialIndex}][${field}]`, value !== null && value !== undefined ? (value || '') : '');
                });
                appendedCount++;
                sequentialIndex++;
            }
        });
        console.log('Appended array data to formData for:', arrayName, 'count:', appendedCount, 'out of', Object.keys(arrayData).length, 'with sequential indices');
    } else {
        // If no data collected at all, don't send the key - backend will keep existing records
        console.log('No array data found for:', arrayName, '- not sending to backend (preserving existing records)');
    }
}

function saveAllSections() {
    const employeeId = $('#edit_employee_user_id').val();
    const sections = ['personal', 'employment', 'emergency', 'family', 'next-of-kin', 'referees', 'education', 'bank', 'statutory'];
    
    Swal.fire({
        title: 'Save All Changes?',
        text: 'This will save all sections. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Disable buttons during save all
            $('#saveCurrentSectionBtn, #saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', true);
            
            // Save each section sequentially
            let currentIndex = 0;
            let savedCount = 0;
            let failedCount = 0;
            
            function saveNext() {
                if (currentIndex >= sections.length) {
                    // Re-enable buttons
                    $('#saveCurrentSectionBtn, #saveAllSectionsBtn, #editEmployeeModal .btn-outline-secondary').prop('disabled', false);
                    
                    if (failedCount === 0) {
                        $('#saveStatusText').html('<span class="text-success"><i class="bx bx-check-circle me-1"></i>All sections saved</span>');
                        Swal.fire({
                            icon: 'success',
                            title: 'All Sections Saved!',
                            text: `All ${savedCount} sections saved successfully!`,
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                        $('#editEmployeeModal').modal('hide');
                        location.reload();
                    });
                    } else {
                        $('#saveStatusText').html(`<span class="text-warning"><i class="bx bx-error me-1"></i>${savedCount} saved, ${failedCount} failed</span>`);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Save Completed',
                            text: `${savedCount} sections saved successfully, ${failedCount} failed.`,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ffc107'
                        });
                    }
                    return;
                }
                
                const section = sections[currentIndex];
                $('#edit_current_section').val(section);
                switchEditTab(section);
                
                setTimeout(() => {
                    // Temporarily override saveCurrentSection to track results
                    const originalSave = saveCurrentSection;
                    const sectionFormData = new FormData();
                    const tabPane = $(`#edit-${section}`);
                    
                    // Collect data for this section (same logic as saveCurrentSection)
                    if (section === 'family') {
                        collectArrayData('family', tabPane, sectionFormData);
                    } else if (section === 'next-of-kin') {
                        collectArrayData('next_of_kin', tabPane, sectionFormData);
                    } else if (section === 'referees') {
                        collectArrayData('referees', tabPane, sectionFormData);
                    } else if (section === 'education') {
                        collectArrayData('educations', tabPane, sectionFormData);
                    } else if (section === 'bank') {
                        collectArrayData('bank_accounts', tabPane, sectionFormData);
                        const primaryAccount = tabPane.find('input[name="primary_bank_account"]:checked').val();
                        if (primaryAccount) {
                            sectionFormData.append('primary_bank_account', primaryAccount);
                        }
                    } else {
                        tabPane.find('input, select, textarea').each(function() {
                            const $input = $(this);
                            const name = $input.attr('name');
                            const type = $input.attr('type');
                            if (!name || (name.includes('[') && name.includes(']'))) return;
                            
                            if (type === 'checkbox') {
                                sectionFormData.append(name, $input.is(':checked') ? ($input.val() || '1') : '0');
                            } else if (type === 'radio' && $input.is(':checked')) {
                                sectionFormData.append(name, $input.val());
                            } else if (type === 'hidden') {
                                sectionFormData.append(name, $input.val());
                            } else {
                                const value = $input.val();
                                if (value !== null && value !== undefined && value !== '') {
                                    sectionFormData.append(name, value);
                                }
                            }
                        });
                    }
                    
                    sectionFormData.append('section', section);
                    sectionFormData.append('_method', 'PUT');
                    
                    $.ajax({
                        url: `/employees/${employeeId}`,
                        type: 'POST',
                        data: sectionFormData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                savedCount++;
                                if (response.employee) {
                                    editEmployeeData = normalizeEmployeeData(response.employee);
                                }
                            } else {
                                failedCount++;
                            }
                    currentIndex++;
                            setTimeout(saveNext, 300);
                        },
                        error: function() {
                            failedCount++;
                            currentIndex++;
                            setTimeout(saveNext, 300);
                        }
                    });
                }, 300);
            }
            saveNext();
        }
    });
}

function openUploadPhotoModal(employeeId) {
    const currentUserId = {{ Auth::id() }};
    const canEdit = {{ Auth::user()->hasAnyRole(['HR Officer', 'System Admin']) ? 'true' : 'false' }};
    
    // Allow if user is HR/Admin or if they're editing their own profile
    if (!canEdit && employeeId != currentUserId) {
        Swal.fire('Error', 'You can only change your own profile picture.', 'error');
        return;
    }
    
    $('#uploadPhotoModal').data('employee-id', employeeId);
    $('#uploadPhotoForm')[0].reset();
    $('#photoPreview').hide();
    $('#uploadPhotoModal').modal('show');
}

function updateEmployee(employeeId, formData) {
    $.ajax({
        url: `/employees/${employeeId}`,
        type: 'PUT',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    $('#editEmployeeModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response.message || 'An error occurred while updating employee.', 'error');
        }
    });
}

function uploadPhoto(employeeId, formData) {
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'jQuery is not loaded. Please refresh the page.',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Error: jQuery is not loaded. Please refresh the page.');
        }
        return;
    }
    
    // Show loading state
    const uploadBtn = $('#uploadPhotoForm').find('button[type="submit"]');
    const originalText = uploadBtn.html();
    uploadBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');
    
    // Disable cancel button during upload
    $('#uploadPhotoModal').find('.btn-outline-secondary').prop('disabled', true);
    
    console.log('Uploading photo for employee:', employeeId);
    console.log('FormData entries:', Array.from(formData.entries()));
    
    $.ajax({
        url: `/employees/${employeeId}/upload-photo`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        timeout: 60000, // 60 second timeout
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    uploadBtn.html(`<span class="spinner-border spinner-border-sm me-2"></span>Uploading ${Math.round(percentComplete)}%...`);
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            uploadBtn.prop('disabled', false).html(originalText);
            $('#uploadPhotoModal').find('.btn-outline-secondary').prop('disabled', false);
            
            console.log('Upload response:', response);
            
            if (response.success) {
                // Show success toast notification
                if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '✓ Upload Successful!',
                        html: `<div class="text-start"><p class="mb-1"><strong>Profile picture</strong> has been uploaded and saved successfully.</p></div>`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#f8f9fa',
                    customClass: {
                        popup: 'swal2-toast-border-success',
                        title: 'text-success fw-bold'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                        toast.style.borderLeft = '4px solid #198754';
                    }
                }).then(() => {
                    $('#uploadPhotoModal').modal('hide');
                    
                    // Update the photo in the images tab without reloading
                    if (response.employee && response.employee.photo) {
                        // Update editEmployeeData
                        if (typeof editEmployeeData !== 'undefined') {
                            editEmployeeData.photo = response.employee.photo;
                                editEmployeeData.photo_url = response.photo_url || response.employee.photo_url;
                        }
                        
                        // Reload images tab if it's currently active
                        const currentSection = $('#edit_current_section').val();
                            if (currentSection === 'images' && typeof loadEditTabContent === 'function') {
                            loadEditTabContent('images', editEmployeeData);
                        }
                        
                        // Update photo in header/profile if visible
                        if (response.photo_url) {
                                $('img[src*="storage/photos"], img[src*="photos"]').each(function() {
                                    $(this).attr('src', response.photo_url + '?t=' + Date.now());
                                });
                        }
                    } else {
                            // Fallback: reload if update failed
                    location.reload();
                    }
                });
            } else {
                    alert('Profile picture uploaded successfully!');
                    $('#uploadPhotoModal').modal('hide');
                    location.reload();
                }
            } else {
                // Show error notification
                const errorMsg = response.message || 'An error occurred while uploading photo.';
                if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: '✗ Upload Failed',
                        html: `<div class="text-start"><p class="mb-0">${errorMsg}</p></div>`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                    customClass: {
                            popup: 'swal2-toast-border-danger',
                        title: 'text-danger fw-bold'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                            toast.style.borderLeft = '4px solid #dc3545';
                    }
                });
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            uploadBtn.prop('disabled', false).html(originalText);
            $('#uploadPhotoModal').find('.btn-outline-secondary').prop('disabled', false);
            
            console.error('Upload error:', {xhr, status, error});
            console.error('Response:', xhr.responseText);
            
            const response = xhr.responseJSON;
            let errorMessage = 'An error occurred while uploading photo.';
            
            if (response) {
                if (response.errors) {
                    // Validation errors
                    const errors = response.errors;
                    let errorList = '<ul class="text-start mb-0 mt-2">';
                    Object.keys(errors).forEach(field => {
                        const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                        messages.forEach(msg => {
                            errorList += `<li><strong>${field.replace(/_/g, ' ')}:</strong> ${msg}</li>`;
                        });
                    });
                    errorList += '</ul>';
                    
                    if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                            title: '✗ Validation Errors',
                            html: `<div class="text-start"><p class="mb-2">Please fix the following errors:</p>${errorList}</div>`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            timer: 8000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'swal2-toast-border-danger',
                                title: 'text-danger fw-bold',
                                htmlContainer: 'text-dark text-start'
                            },
                            width: '450px',
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                                toast.style.borderLeft = '4px solid #dc3545';
                            }
                        });
                    } else {
                        alert('Validation Errors: ' + errorList);
                    }
                    return;
                } else if (response.message) {
                    errorMessage = response.message;
                } else if (response.error) {
                    errorMessage = response.error;
                }
            } else if (xhr.status === 0) {
                errorMessage = 'Network error. Please check your internet connection.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again or contact support.';
                if (response && response.error) {
                    errorMessage += ' Error: ' + response.error;
                }
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to upload photos.';
            } else if (xhr.status === 404) {
                errorMessage = 'Employee not found. Please refresh and try again.';
            } else if (xhr.status === 422) {
                errorMessage = 'Validation error. Please check your file format and size.';
            }
            
            // Show error toast notification
            if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                    title: '✗ Upload Failed',
                text: errorMessage,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-toast-border-danger',
                        title: 'text-danger fw-bold',
                        htmlContainer: 'text-dark'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                        toast.style.borderLeft = '4px solid #dc3545';
                    }
                });
            } else {
                alert('Error: ' + errorMessage);
            }
        }
    });
}

function toggleEmployeeStatus(employeeId) {
    Swal.fire({
        title: 'Change Employee Status?',
        text: 'Are you sure you want to change this employee\'s status?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Change',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/employees/${employeeId}/toggle-status`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire('Error', response.message || 'An error occurred while changing status.', 'error');
                }
            });
        }
    });
}

@if($canEditAll)
function createEmployee(formData) {
    $.ajax({
        url: `/employees`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    $('#addEmployeeModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'An error occurred while creating employee.';
            if (response && response.errors) {
                const errorList = Object.values(response.errors).flat().join('\\n');
                errorMessage = errorList || errorMessage;
            } else if (response && response.message) {
                errorMessage = response.message;
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    });
    }
@endif

// Send SMS to employee
function sendEmployeeSMS(employeeId) {
    if (!employeeId) {
        Swal.fire('Error', 'Invalid employee ID.', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Send SMS to Employee?',
        text: 'This will send a welcome SMS with login credentials to the employee\'s phone number.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send SMS',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("employees.send-sms", ":id") }}'.replace(':id', employeeId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to send SMS');
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
                title: 'SMS Sent!',
                text: result.value.message || 'SMS has been sent successfully to the employee.',
                timer: 3000
            });
        }
    });
}
</script>

<style>
/* Advanced Employee Card Styles */
.employee-card {
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    border-color: #696cff;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
}

.employee-card .avatar img {
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.employee-card .progress {
    border-radius: 10px;
    background-color: #f0f0f0;
}

.employee-card .badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}

/* View Toggle Buttons */
.btn-group .btn.active {
    background-color: #696cff;
    color: white;
    border-color: #696cff;
}

/* Pagination Styling - Fix large icons */
.pagination-wrapper .pagination {
    margin-bottom: 0;
}

.pagination-wrapper .pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    min-width: 38px;
    text-align: center;
}

.pagination-wrapper .pagination .page-link i,
.pagination-wrapper .pagination .page-link svg {
    font-size: 0.875rem;
    width: 0.875rem;
    height: 0.875rem;
    vertical-align: middle;
}

.pagination-wrapper .pagination .page-item:first-child .page-link,
.pagination-wrapper .pagination .page-item:last-child .page-link {
    padding: 0.375rem 0.5rem;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #696cff;
    border-color: #696cff;
}

/* Fix modal z-index to ensure they appear above dropdowns and other elements */
.modal {
    z-index: 1060 !important;
}

.modal-backdrop {
    z-index: 1055 !important;
}

#employeeDetailsModal {
    z-index: 1060 !important;
}

#editEmployeeModal {
    z-index: 1070 !important;
}

#editEmployeeModal .modal-content {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

#editEmployeeModal .modal-body {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

#editEmployeeModal .nav-tabs {
    border-bottom: 2px solid #dee2e6;
    background-color: #f8f9fa;
}

#editEmployeeModal .nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

#editEmployeeModal .nav-tabs .nav-link:hover {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

#editEmployeeModal .nav-tabs .nav-link.active {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
    background-color: transparent;
    font-weight: 600;
}

#editEmployeeModal .modal-footer {
    border-top: 2px solid #dee2e6;
    background-color: #f8f9fa;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
}

#uploadPhotoModal {
    z-index: 1075 !important;
}

#addEmployeeModal {
    z-index: 1080 !important;
}

/* Ensure dropdowns in modals have proper z-index */
.modal .dropdown-menu {
    z-index: 1085 !important;
}

.modal .dropdown {
    position: relative;
    z-index: 1084 !important;
}

/* Fix all dropdown menus throughout the page */
.dropdown-menu {
    z-index: 1052 !important;
}

.employee-card .dropdown,
#employeeTable .dropdown {
    position: relative;
}

.employee-card .dropdown-menu,
#employeeTable .dropdown-menu {
    position: absolute !important;
    z-index: 1052 !important;
    margin-top: 0.125rem !important;
}

/* Enhanced Add Employee Modal Styling */
#addEmployeeModal .modal-dialog {
    max-width: 95%;
}

#addEmployeeModal .modal-header {
    background: linear-gradient(135deg, #696cff 0%, #5a5fd8 100%);
    border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

#addEmployeeModal .modal-body {
    background: #f8f9fa;
}

#addEmployeeModal .nav-tabs {
    border-bottom: 2px solid #dee2e6;
    background: white;
    padding: 0.5rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

#addEmployeeModal .nav-tabs .nav-link {
    border: none;
    border-radius: 0.375rem;
    margin: 0 0.25rem;
    transition: all 0.3s ease;
    color: #6c757d;
}

#addEmployeeModal .nav-tabs .nav-link:hover {
    background: #f8f9fa;
    color: #696cff;
}

#addEmployeeModal .nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #696cff 0%, #5a5fd8 100%);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(105, 108, 255, 0.3);
}

#addEmployeeModal .card {
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-radius: 0.75rem;
}

#addEmployeeModal .progress {
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

#addEmployeeModal .progress-bar {
    transition: width 0.6s ease;
}

#addEmployeeModal .badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    transition: all 0.3s ease;
}

#addEmployeeModal .badge.bg-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a5fd8 100%) !important;
}

#addEmployeeModal .badge.bg-secondary:hover {
    background: #6c757d !important;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

#addEmployeeModal .form-control:focus,
#addEmployeeModal .form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

#addEmployeeModal .btn-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a5fd8 100%);
    border: none;
    box-shadow: 0 2px 8px rgba(105, 108, 255, 0.3);
    transition: all 0.3s ease;
}

#addEmployeeModal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
}

#addEmployeeModal .btn-outline-primary {
    border-color: #696cff;
    color: #696cff;
    transition: all 0.3s ease;
}

#addEmployeeModal .btn-outline-primary:hover {
    background: #696cff;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
}

#addEmployeeModal .modal-footer {
    background: white;
    border-top: 2px solid #dee2e6;
    padding: 1.25rem;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

/* Toast notification styles */
.swal2-toast-border-success {
    border-left: 4px solid #198754 !important;
}

.swal2-toast-border-danger {
    border-left: 4px solid #dc3545 !important;
}

.swal2-toast-border-warning {
    border-left: 4px solid #ffc107 !important;
}

.swal2-toast-border-info {
    border-left: 4px solid #0dcaf0 !important;
}

/* Enhanced Quick Actions Dropdown */
.dropdown-menu {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem;
}

.dropdown-item {
    border-radius: 0.375rem;
    padding: 0.625rem 1rem;
    transition: all 0.2s ease;
    margin-bottom: 0.25rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(4px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

/* Enhanced Action Buttons */
.btn-group-sm .btn {
    transition: all 0.2s ease;
}

.btn-group-sm .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Enhanced Employee Table */
#employeeTable {
    font-size: 0.9rem;
}

#employeeTable thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 1rem 0.75rem;
}

#employeeTable tbody tr {
    transition: all 0.2s ease;
}

#employeeTable tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Enhanced Employee Cards */
.employee-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    border-color: #696cff;
}

.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
}
</style>

@push('scripts')
<script>
// Quick Actions Functions
function exportEmployees() {
    Swal.fire({
        title: 'Export Employee Data',
        html: `
            <div class="text-start">
                <p class="mb-3">Choose export format:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success w-100" onclick="exportToExcel()" style="padding: 0.75rem;">
                        <i class="bx bx-file me-2"></i>Export to Excel (.xlsx)
                    </button>
                    <button class="btn btn-danger w-100" onclick="exportToPDF()" style="padding: 0.75rem;">
                        <i class="bx bx-file me-2"></i>Export to PDF
                    </button>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        showConfirmButton: false,
        width: '450px'
    });
}

function exportToExcel() {
    Swal.close();
    Swal.fire({
        icon: 'info',
        title: 'Exporting to Excel...',
        text: 'Please wait while we prepare your file.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    window.location.href = '{{ route("modules.hr.employees") }}?export=excel';
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Export Started',
            text: 'Your Excel file will download shortly.',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}

function exportToPDF() {
    Swal.close();
    Swal.fire({
        icon: 'info',
        title: 'Exporting to PDF...',
        text: 'Please wait while we prepare your file.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    window.location.href = '{{ route("modules.hr.employees") }}?export=pdf';
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Export Started',
            text: 'Your PDF file will download shortly.',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}

function bulkActions() {
    const selectedEmployees = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedEmployees.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one employee to perform bulk actions.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Bulk Actions',
        html: `
            <div class="text-start">
                <p class="mb-3"><strong>${selectedEmployees.length}</strong> employee(s) selected</p>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkActivate()">
                        <i class="bx bx-check-circle me-2 text-success"></i>Activate Selected
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkDeactivate()">
                        <i class="bx bx-x-circle me-2 text-danger"></i>Deactivate Selected
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkExport()">
                        <i class="bx bx-download me-2 text-primary"></i>Export Selected
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkSendSMS()">
                        <i class="bx bx-message me-2 text-info"></i>Send SMS to Selected
                    </button>
                    <hr class="dropdown-divider">
                    <button type="button" class="list-group-item list-group-item-action text-warning" onclick="bulkGeneratePasswordsAndSendSMS()">
                        <i class="bx bx-key me-2"></i>Generate Passwords & Send SMS to All
                    </button>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        showConfirmButton: false,
        width: '500px'
    });
}

function bulkActivate() {
    const selected = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    Swal.fire({
        title: 'Activate Employees?',
        text: `Are you sure you want to activate ${selected.length} employee(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("modules.hr.employees.bulk-action") }}',
                method: 'POST',
                data: {
                    action: 'activate',
                    employee_ids: selected,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to activate employees.', 'error');
                }
            });
        }
    });
}

function bulkDeactivate() {
    const selected = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    Swal.fire({
        title: 'Deactivate Employees?',
        text: `Are you sure you want to deactivate ${selected.length} employee(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Deactivate',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("modules.hr.employees.bulk-action") }}',
                method: 'POST',
                data: {
                    action: 'deactivate',
                    employee_ids: selected,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to deactivate employees.', 'error');
                }
            });
        }
    });
}

function bulkExport() {
    const selected = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    window.location.href = '{{ route("modules.hr.employees") }}?export=excel&ids=' + selected.join(',');
    Swal.fire({
        icon: 'success',
        title: 'Exporting...',
        text: 'Your file will download shortly.',
        timer: 2000,
        showConfirmButton: false
    });
}

function bulkSendSMS() {
    const selected = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selected.length === 0) {
        Swal.fire('Warning', 'Please select at least one employee.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Send SMS to Selected Employees',
        html: `
            <div class="mb-3">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="sendCredentials" checked>
                    <label class="form-check-label" for="sendCredentials">
                        <strong>Send Login Credentials</strong> (Generate password and send welcome SMS with username/password)
                    </label>
                </div>
                <div class="mb-3" id="customMessageDiv">
                    <label class="form-label">Custom Message (Optional)</label>
                    <textarea id="bulkSMSMessage" class="form-control" rows="4" placeholder="Enter custom message here... (Leave empty if sending credentials)"></textarea>
                    <small class="text-muted">If credentials are enabled, this will be ignored. Otherwise, this message will be sent.</small>
                </div>
                <small class="text-muted d-block">${selected.length} employee(s) will receive this message</small>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Send SMS',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            $('#sendCredentials').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#customMessageDiv').hide();
                } else {
                    $('#customMessageDiv').show();
                }
            });
            $('#sendCredentials').trigger('change');
        },
        preConfirm: () => {
            const sendCredentials = $('#sendCredentials').is(':checked');
            const message = $('#bulkSMSMessage').val();
            
            if (!sendCredentials && !message.trim()) {
                Swal.showValidationMessage('Please enter a message or enable credentials sending');
                return false;
            }
            
            return {
                sendCredentials: sendCredentials,
                message: message
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sending SMS...',
                text: 'Please wait while we send SMS to all selected employees.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("modules.hr.employees.bulk-sms") }}',
                method: 'POST',
                data: {
                    employee_ids: selected,
                    message: result.value.message,
                    send_credentials: result.value.sendCredentials ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        let message = response.message;
                        if (response.results && response.results.length > 0) {
                            let details = '<div class="text-start mt-3"><strong>Details:</strong><ul class="mt-2">';
                            response.results.slice(0, 10).forEach(function(r) {
                                const statusIcon = r.status === 'success' ? '✓' : '✗';
                                const statusColor = r.status === 'success' ? 'text-success' : 'text-danger';
                                details += `<li class="${statusColor}">${statusIcon} ${r.employee} - ${r.status === 'success' ? 'Sent' : r.reason || 'Failed'}</li>`;
                            });
                            if (response.results.length > 10) {
                                details += `<li class="text-muted">... and ${response.results.length - 10} more</li>`;
                            }
                            details += '</ul></div>';
                            message += details;
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'SMS Sent!',
                            html: message,
                            width: '600px'
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to send SMS.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
}

function bulkGeneratePasswordsAndSendSMS() {
    Swal.fire({
        title: 'Generate Passwords & Send SMS to All Employees?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will:</p>
                <ul class="text-start mb-3">
                    <li>Generate a new password for all employees with phone numbers</li>
                    <li>Update their passwords in the database</li>
                    <li>Send SMS with login credentials (username and password) to their <strong>phone</strong> field</li>
                </ul>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action will reset passwords for all employees and send SMS to all employees with phone numbers. This may take several minutes.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Generate & Send',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            return new Promise((resolve) => {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Generating passwords and sending SMS. This may take a while.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("modules.hr.employees.bulk-generate-passwords-sms") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            let message = response.message;
                            if (response.results && response.results.length > 0) {
                                let details = '<div class="text-start mt-3"><strong>Results Summary:</strong><ul class="mt-2" style="max-height: 300px; overflow-y: auto;">';
                                response.results.forEach(function(r) {
                                    const statusIcon = r.status === 'success' ? '✓' : '✗';
                                    const statusColor = r.status === 'success' ? 'text-success' : 'text-danger';
                                    details += `<li class="${statusColor}">${statusIcon} ${r.employee} (${r.email}) - ${r.status === 'success' ? 'Sent to ' + r.phone : r.reason || 'Failed'}</li>`;
                                });
                                details += '</ul></div>';
                                message += details;
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Completed!',
                                html: message,
                                width: '700px'
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                        resolve();
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Failed to generate passwords and send SMS.';
                        Swal.fire('Error', errorMsg, 'error');
                        resolve();
                    }
                });
            });
        }
    });
}

function generateReport() {
    Swal.fire({
        title: 'Generate Employee Report',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label fw-bold">Report Type</label>
                    <select id="reportType" class="form-select form-select-lg">
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="department">Department Report</option>
                        <option value="salary">Salary Report</option>
                        <option value="completion">Profile Completion Report</option>
                    </select>
                    <small class="text-muted">Select the type of report you want to generate</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Export Format</label>
                    <select id="reportFormat" class="form-select form-select-lg">
                        <option value="pdf">PDF Document</option>
                        <option value="excel">Excel Spreadsheet</option>
                    </select>
                    <small class="text-muted">Choose your preferred file format</small>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-bar-chart me-1"></i>Generate Report',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#17a2b8',
        width: '500px',
        preConfirm: () => {
            const type = document.getElementById('reportType').value;
            const format = document.getElementById('reportFormat').value;
            if (!type || !format) {
                Swal.showValidationMessage('Please select both report type and format');
                return false;
            }
            return { type, format };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'info',
                title: 'Generating Report...',
                text: 'Please wait while we prepare your report.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const url = '{{ route("modules.hr.employees.report") }}?type=' + result.value.type + '&format=' + result.value.format;
            
            // Open in new tab
            const newWindow = window.open(url, '_blank');
            
            setTimeout(() => {
                if (newWindow) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Generated',
                        text: 'Your report is opening in a new tab.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Blocked',
                        text: 'Please allow popups for this site and try again.',
                        confirmButtonText: 'OK'
                    });
                }
            }, 1500);
        }
    });
}

function refreshData() {
    Swal.fire({
        title: 'Refreshing Data...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Please wait while we refresh the employee data.</p>
            </div>
        `,
        icon: null,
        allowOutsideClick: false,
        showConfirmButton: false,
        showCancelButton: false,
        didOpen: () => {
            // Reload the page after a short delay
            setTimeout(() => {
                location.reload();
            }, 800);
        }
    });
}

// Select All Employees
$(document).ready(function() {
    $('#selectAllEmployees').on('change', function() {
        $('.employee-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    $('.employee-checkbox').on('change', function() {
        const total = $('.employee-checkbox').length;
        const checked = $('.employee-checkbox:checked').length;
        $('#selectAllEmployees').prop('checked', total === checked);
    });
});
</script>
@endpush