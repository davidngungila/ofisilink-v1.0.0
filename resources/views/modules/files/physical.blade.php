@extends('layouts.app')

@section('title', 'Physical File Management')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-archive"></i> Physical Rack Management
                </h4>
                <p class="text-muted">Manage physical files stored in racks with tracking and request system</p>
            </div>
            <div>
                @if($canManageFiles)
                    <button class="btn btn-primary" id="create-rack-folder-btn">
                        <i class="bx bx-folder-plus"></i> Create Rack Folder
                    </button>
                    <button class="btn btn-success" id="create-rack-file-btn">
                        <i class="bx bx-file"></i> Create Rack File
                    </button>
                    <button class="btn btn-secondary" id="manage-categories-btn">
                        <i class="bx bx-category"></i> Manage Categories
                    </button>
                @endif
                <button class="btn btn-info" id="search-rack-btn">
                    <i class="bx bx-search"></i> Search Rack
                </button>
                @if($isStaff)
                    <button class="btn btn-primary" id="my-requests-btn">
                        <i class="bx bx-clipboard"></i> My Requests
                    </button>
                @endif
                @if($canManageFiles && $stats['pending_requests'] > 0)
                    <button class="btn btn-warning" id="rack-pending-requests-btn">
                        <i class="bx bx-time"></i> Pending Requests 
                        <span class="badge bg-danger">{{ $stats['pending_requests'] }}</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .rack-folder-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .rack-folder-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    .activity-timeline {
        position: relative;
        padding-left: 1rem;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    /* Ensure SweetAlert is above Bootstrap modals */
    .swal2-container { z-index: 200000 !important; }
    /* Ensure Return File Modal appears on top of all modals */
    #returnFileModal {
        z-index: 1065 !important;
    }
    #returnFileModal .modal-dialog {
        z-index: 1065 !important;
    }
    body.modal-open #returnFileModal {
        z-index: 1065 !important;
    }
    /* Ensure Request Physical File Modal appears on top of all modals */
    #requestPhysicalFileModal {
        z-index: 1070 !important;
    }
    #requestPhysicalFileModal .modal-dialog {
        z-index: 1070 !important;
    }
    body.modal-open #requestPhysicalFileModal {
        z-index: 1070 !important;
    }
    body.modal-open #requestPhysicalFileModal ~ .modal-backdrop {
        z-index: 1069 !important;
    }
</style>
@endpush

@section('content')
<!-- Dashboard Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-folder text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <span class="badge bg-label-primary">Active</span>
                </div>
                <span class="fw-semibold d-block mb-1">Total Folders</span>
                <h3 class="card-title mb-2">{{ $stats['total_folders'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-file text-success" style="font-size: 2rem;"></i>
                    </div>
                    <span class="badge bg-label-success">Total</span>
                </div>
                <span class="fw-semibold d-block mb-1">Physical Files</span>
                <h3 class="card-title mb-2">{{ $stats['total_files'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-share text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <span class="badge bg-label-warning">Issued</span>
                </div>
                <span class="fw-semibold d-block mb-1">Issued Files</span>
                <h3 class="card-title mb-2">{{ $stats['issued_files'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-time text-info" style="font-size: 2rem;"></i>
                    </div>
                    <span class="badge bg-label-info">Pending</span>
                </div>
                <span class="fw-semibold d-block mb-1">Pending Requests</span>
                <h3 class="card-title mb-2">{{ $stats['pending_requests'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Rack Management Content -->
    <div class="row">
    <!-- Rack Navigation -->
    <div class="col-md-4">
        <!-- Categories Filter -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bx bx-category"></i> Rack Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group" id="rackCategoriesList">
                    <a href="#" class="list-group-item list-group-item-action active" data-category-id="0">
                        <i class="bx bx-folder-open me-2"></i> All Categories
                    </a>
                    @foreach($rackCategories as $category)
                    <a href="#" class="list-group-item list-group-item-action" data-category-id="{{ $category->id }}">
                        <div class="fw-semibold">{{ $category->name }}</div>
                        <small class="text-muted">{{ $category->description }}</small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Rack Folders -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bx bx-folder"></i> Recent Folders</h5>
            </div>
            <div class="card-body">
                @foreach($rackFolders as $folder)
                <div class="d-flex align-items-center mb-3 p-3 border rounded">
                    <i class="bx bx-folder text-warning me-3" style="font-size: 2rem;"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $folder->name }}</div>
                        <small class="text-muted">
                            {{ $folder->rack_number }} • {{ $folder->category->name }}
                        </small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary btn-view-rack-folder" data-folder-id="{{ $folder->id }}">
                        <i class="bx bx-show"></i>
                    </button>
                </div>
                @endforeach
                @if($rackFolders->isEmpty())
                <p class="text-muted small text-center">No rack folders yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Rack Contents -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="currentRackTitle">
                    <i class="bx bx-archive"></i> Rack System Overview
                </h5>
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Type to search files, racks, categories..." id="rackSearchInput" autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display:none;" title="Clear search">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="rackContents">
                    <div class="text-center py-5">
                        <i class="bx bx-archive" style="font-size: 4rem; color: #d0d0d0;"></i>
                        <h5 class="text-muted mt-3">Select a category or search to view rack folders</h5>
                        <p class="text-muted">Manage physical file storage with complete tracking</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Rack Folder Modal -->
<div class="modal fade" id="createRackFolderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-folder-plus"></i> Create Rack Folder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRackFolderForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="create_rack_folder">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Folder Name *</label>
                                <input type="text" name="folder_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($rackCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rack Range Start *</label>
                                <input type="number" name="rack_range_start" class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rack Range End *</label>
                                <input type="number" name="rack_range_end" class="form-control" min="1" value="60" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Access Level *</label>
                                <select name="access_level" class="form-select">
                                    <option value="public">Public (All Users)</option>
                                    <option value="department">Department</option>
                                    <option value="private">Private (Restricted)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g., Room 101, Shelf A">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Rack Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Rack File Modal -->
<div class="modal fade" id="createRackFileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-file"></i> Create Rack File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRackFileForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="create_rack_file">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Rack Folder *</label>
                        <select name="folder_id" class="form-select" id="rackFolderSelect" required>
                            <option value="">Select Rack Folder</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">File Name *</label>
                                <input type="text" name="file_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">File Number *</label>
                                <input type="text" name="file_number" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Type</label>
                                <select name="file_type" class="form-select">
                                    <option value="general">General</option>
                                    <option value="contract">Contract</option>
                                    <option value="financial">Financial</option>
                                    <option value="legal">Legal</option>
                                    <option value="hr">HR</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Confidentiality Level</label>
                                <select name="confidential_level" class="form-select">
                                    <option value="normal">Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="strictly_confidential">Strictly Confidential</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Date</label>
                                <input type="date" name="file_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" class="form-control" placeholder="comma,separated,tags">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Retention Period (Years)</label>
                                <input type="number" name="retention_period" class="form-control" min="1" max="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Rack File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Physical File Modal -->
<div class="modal fade" id="requestPhysicalFileModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-dark">
                <h5 class="modal-title text-white"><i class="bx bx-hand"></i> Request Physical File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="requestPhysicalFileForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="request_physical_file">
                    <input type="hidden" name="file_id" id="request_physical_file_id">
                    
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <p class="form-control-plaintext fw-semibold" id="request_physical_file_name"></p>
                        <p class="text-muted small" id="request_physical_file_location"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose for Request *</label>
                        <textarea name="purpose" id="request_purpose" class="form-control" rows="3" required minlength="10" maxlength="500"></textarea>
                        <small class="text-muted" id="purpose_char_counter">
                            <span id="purpose_char_count">0</span>/500 characters (minimum 10 characters required)
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expected Return Date</label>
                                <input type="date" name="expected_return_date" class="form-control" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Urgency *</label>
                                <select name="urgency" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit File Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return File Modal -->
<div class="modal fade" id="returnFileModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-undo"></i> Return Physical File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnFileForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="return_physical_file">
                    <input type="hidden" name="file_id" id="return_file_id">
                    
                    <div class="mb-3">
                        <label class="form-label">File Name</label>
                        <p class="form-control-plaintext fw-semibold" id="return_file_name">-</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Number</label>
                        <p class="form-control-plaintext text-muted" id="return_file_number">-</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Return Condition *</label>
                        <select name="return_condition" class="form-select" required>
                            <option value="excellent">Excellent - No issues</option>
                            <option value="good" selected>Good - Minor wear</option>
                            <option value="fair">Fair - Noticeable wear</option>
                            <option value="poor">Poor - Significant damage</option>
                            <option value="damaged">Damaged - Requires attention</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Return Notes</label>
                        <textarea name="return_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Return File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- My Requests Modal -->
<div class="modal fade" id="myRequestsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-clipboard"></i> My File Requests</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="myRequestsContent" class="table-responsive"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Pending Rack Requests Modal -->
<div class="modal fade" id="pendingRackRequestsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-time"></i> Pending Rack Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="pendingRackRequestsContent" class="table-responsive"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveRackRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-check"></i> Approve Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveRackRequestForm">
                <div class="modal-body">
                    <input type="hidden" id="approve_request_id" name="request_id">
                    <div class="mb-3">
                        <label for="approval-notes" class="form-label">Approval Notes <small class="text-muted">(Optional)</small></label>
                        <textarea class="form-control" id="approval-notes" name="notes" rows="4" placeholder="Optional approval notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div class="modal fade" id="rejectRackRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-x"></i> Reject Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectRackRequestForm">
                <div class="modal-body">
                    <input type="hidden" id="reject_request_id" name="request_id">
                    <div class="mb-3">
                        <label for="rejection-notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection-notes" name="notes" rows="4" placeholder="Please provide a reason for rejection..." required></textarea>
                        <div class="invalid-feedback">Please provide a reason for rejection.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Search Rack Modal -->
<div class="modal fade" id="searchRackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-search"></i> Search Rack System</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="searchRackForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="live_search_rack_files">
                    
                    <div class="mb-3">
                        <label class="form-label">Search Query *</label>
                        <input type="text" name="query" id="modalSearchQuery" class="form-control" placeholder="Type to search files..." autocomplete="off">
                        <small class="text-muted">Live search - results appear as you type</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="modalSearchCategory" class="form-select">
                            <option value="0">All Categories</option>
                            @foreach($rackCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Live Search Results Container -->
                    <div id="modalSearchResults" class="mt-3" style="display: none;">
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            <div id="modalSearchResultsContent"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="clearModalSearchBtn" style="display: none;">Clear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-category"></i> Manage Rack Categories</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Rack Categories</h6>
                    <button class="btn btn-primary btn-sm" id="add-category-btn">
                        <i class="bx bx-plus"></i> Add Category
                    </button>
                </div>
                
                    <div class="table-responsive">
                    <table class="table table-striped" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Prefix</th>
                                <th>Status</th>
                                <th>Folders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                            <tbody>
                            @foreach($rackCategories as $category)
                            <tr data-category-id="{{ $category->id }}">
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? 'N/A' }}</td>
                                <td><span class="badge bg-info">{{ $category->prefix }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $category->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td>{{ $category->folders()->count() }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-category-btn" data-category-id="{{ $category->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-category-btn" data-category-id="{{ $category->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="categoryModalTitle"><i class="bx bx-category"></i> Add Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" id="categoryAction" value="create_category">
                    <input type="hidden" name="category_id" id="categoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" id="categoryName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="categoryDescription" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Prefix *</label>
                        <input type="text" name="prefix" id="categoryPrefix" class="form-control" maxlength="10" required>
                        <small class="form-text text-muted">Used for generating rack numbers (e.g., DOC, FIN, HR)</small>
                    </div>
                    
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label class="form-label">Status *</label>
                        <select name="status" id="categoryStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const canManageFiles = {{ $canManageFiles ? 'true' : 'false' }};
    const isStaff = {{ $isStaff ? 'true' : 'false' }};
    const currentUserId = {{ auth()->id() ?? 'null' }};
    let currentRackCategoryId = 0;

    // Rack Categories Navigation
    $('#rackCategoriesList a').on('click', function(e) {
        e.preventDefault();
        const categoryId = $(this).data('category-id');
        loadRackFolders(categoryId);
        
        $('#rackCategoriesList a').removeClass('active');
        $(this).addClass('active');
    });

    // Create Rack Folder
    if (canManageFiles) {
        $('#create-rack-folder-btn').on('click', function() {
            $('#createRackFolderModal').modal('show');
        });

        $('#createRackFolderForm').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), 'Creating rack folder...', function(response) {
                $('#createRackFolderModal').modal('hide');
                Swal.fire('Success!', response.message, 'success');
                loadRackFolders(currentRackCategoryId);
            });
        });
    }

    // View Folder Button Handler
    $('.btn-view-rack-folder').on('click', function() {
        const folderId = $(this).data('folder-id');
        loadRackFolderContents(folderId);
    });

    function loadRackFolders(categoryId = 0, search = '') {
        currentRackCategoryId = categoryId;
        
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'get_rack_folders',
                category_id: categoryId,
                search: search,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    displayRackFolders(response.folders, categoryId, search);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }

    function displayRackFolders(folders, categoryId, search) {
        let html = '';
        
        if (folders.length === 0) {
            html = `
                <div class="text-center py-5">
                    <i class="bx bx-folder-open" style="font-size: 4rem; color: #d0d0d0;"></i>
                    <h5 class="text-muted mt-3">No rack folders found</h5>
                    ${search ? '<p class="text-muted">Try adjusting your search terms</p>' : '<p class="text-muted">Create rack folders to get started</p>'}
                </div>
            `;
        } else {
            html = '<div class="row">';
            
            folders.forEach(folder => {
                // Use files_count (from withCount) or file_count (fallback), default to 0
                const fileCount = Number(folder.files_count || folder.file_count || 0);
                const issuedCount = Number(folder.issued_count || 0);
                // Use available_count from query, or calculate it
                const availableFiles = Number(folder.available_count || Math.max(0, fileCount - issuedCount));
                const categoryName = folder.category_name || (folder.category ? folder.category.name : '');
                
                html += `
                    <div class="col-lg-6 mb-4">
                        <div class="card rack-folder-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title mb-1">${folder.name}</h6>
                                        <p class="text-muted small mb-0">${categoryName || '-'}</p>
                                    </div>
                                    <span class="badge bg-primary">${folder.rack_number}</span>
                                </div>
                                
                                <p class="card-text small text-muted">${folder.description || 'No description'}</p>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="text-primary fw-semibold">${fileCount}</div>
                                        <small class="text-muted">Total Files</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success fw-semibold">${availableFiles}</div>
                                        <small class="text-muted">Available</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-warning fw-semibold">${issuedCount}</div>
                                        <small class="text-muted">Issued</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        <i class="bx bx-map"></i> ${folder.location || 'Not specified'}
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary btn-view-rack-folder" data-folder-id="${folder.id}">
                                        <i class="bx bx-show"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        }
        
        $('#rackContents').html(html);
        
        // Re-attach event handlers
        $('.btn-view-rack-folder').on('click', function() {
            const folderId = $(this).data('folder-id');
            loadRackFolderContents(folderId);
        });
    }

    function loadRackFolderContents(folderId) {
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'get_rack_folder_contents',
                folder_id: folderId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    displayRackFolderContents(response);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }

    function displayRackFolderContents(data) {
        const folder = data.folder;
        const files = data.files || [];
        const activities = data.activities || [];
        const stats = data.stats || {};
        const currentUserId = data.current_user_id || window.currentUserId || null;
        
        // Get accurate counts from stats or calculate from files array
        const totalFiles = stats.total_files || folder.files_count || files.length;
        const issuedFiles = stats.issued_files || folder.issued_count || files.filter(f => f.status === 'issued').length;
        const availableFiles = stats.available_files || folder.available_count || files.filter(f => f.status === 'available').length;
        
        let html = `
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5>${folder.name}</h5>
                        <p class="text-muted mb-1">
                            <strong>Rack Number:</strong> ${folder.rack_number} | 
                            <strong>Category:</strong> ${(folder.category && folder.category.name) ? folder.category.name : (folder.category_name || '-')} |
                            <strong>Location:</strong> ${folder.location || 'Not specified'}
                        </p>
                        <p class="text-muted">${folder.description || 'No description'}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-${folder.access_level === 'public' ? 'success' : folder.access_level === 'private' ? 'danger' : 'primary'}">
                            ${folder.access_level} Access
                        </span>
                        <br>
                        <small class="text-muted">Rack Range: ${folder.rack_range_start}-${folder.rack_range_end}</small>
                    </div>
                </div>
            </div>
        `;
        
        // Rack System Overview Stats
        html += `
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Files</h6>
                            <h3 class="mb-0 text-primary">${totalFiles}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Available</h6>
                            <h3 class="mb-0 text-success">${availableFiles}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Issued</h6>
                            <h3 class="mb-0 text-warning">${issuedFiles}</h3>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Files section
        html += `
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6><i class="bx bx-file"></i> Files in this Rack</h6>
                    <span class="badge bg-secondary">${files.length} files displayed</span>
                </div>
        `;
        
        if (!Array.isArray(files) || files.length === 0) {
            html += `
                <div class="text-center py-4 border rounded">
                    <i class="bx bx-file" style="font-size: 3rem; color: #d0d0d0;"></i>
                    <h6 class="text-muted">No files in this rack yet</h6>
                </div>
            `;
        } else {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>File Number</th><th>File Name</th><th>Type</th><th>Status</th><th>Current Holder</th><th>Actions</th></tr></thead><tbody>';
            
            files.forEach(file => {
                const statusBadge = file.status === 'available' ? 'bg-success' : 
                                  file.status === 'issued' ? 'bg-warning' : 'bg-secondary';
                const statusText = file.status === 'available' ? 'Available' : 
                                 file.status === 'issued' ? 'Issued' : 'Archived';
                
                // Check if current user is the holder
                const holderId = file.holder ? (file.holder.id || file.current_holder) : (file.current_holder || null);
                // Compare as integers to ensure proper comparison
                const isCurrentHolder = currentUserId && holderId && parseInt(currentUserId) === parseInt(holderId);
                
                // Build actions HTML
                let actionsHtml = '';
                if (file.status === 'available') {
                    actionsHtml = `
                        <button class="btn btn-sm btn-outline-warning btn-request-physical-file" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name}">
                            <i class="bx bx-hand-up"></i> Request
                        </button>
                    `;
                } else if (file.status === 'issued' && isCurrentHolder) {
                    // Show Return button if current user is the holder
                    actionsHtml = `
                        <button class="btn btn-sm btn-success btn-return-file-from-list" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name || 'Unknown'}"
                                data-file-number="${file.file_number || '-'}">
                            <i class="bx bx-undo"></i> Return
                        </button>
                    `;
                }
                
                html += `
                    <tr>
                        <td><strong>${file.file_number || '-'}</strong></td>
                        <td>${file.file_name || 'Unknown'}</td>
                        <td>${file.file_type || '-'}</td>
                        <td><span class="badge ${statusBadge}">${statusText}</span></td>
                        <td>${file.holder ? (file.holder.name || (file.holder.first_name && file.holder.last_name ? file.holder.first_name + ' ' + file.holder.last_name : 'Unknown')) : '-'}</td>
                        <td>${actionsHtml}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        html += '</div>';
        
        $('#rackContents').html(html);
        
        // Attach event handlers for return buttons from the list
        $('.btn-return-file-from-list').on('click', function() {
            const fileId = $(this).data('file-id');
            const fileName = $(this).data('file-name') || 'Unknown File';
            const fileNumber = $(this).data('file-number') || '-';
            
            $('#return_file_id').val(fileId);
            $('#return_file_name').text(fileName || 'Unknown File');
            $('#return_file_number').text(fileNumber || '-');
            
            // Show return modal on top
            $('#returnFileModal').modal('show');
            
            // Force z-index after modal is shown
            setTimeout(function() {
                $('#returnFileModal').css('z-index', '1065');
                $('.modal-backdrop').last().css('z-index', '1064');
            }, 100);
        });
    }

    function submitForm(form, loadingText, successCallback) {
        Swal.fire({
            title: loadingText,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: form.serialize(),
            success: function(response) {
                Swal.close();
                if (response.success) {
                    successCallback(response);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'An error occurred. Please try again.';
                
                if (xhr.status === 422) {
                    // Validation error
                    const errors = xhr.responseJSON?.errors || xhr.responseJSON;
                    if (errors && typeof errors === 'object') {
                        const errorMessages = [];
                        for (let field in errors) {
                            if (Array.isArray(errors[field])) {
                                errorMessages.push(...errors[field]);
                            } else {
                                errorMessages.push(errors[field]);
                            }
                        }
                        errorMessage = errorMessages.join('<br>');
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage
                });
            }
        });
    }

    // Create Rack File
    if (canManageFiles) {
        $('#create-rack-file-btn').on('click', function() {
            loadRackFoldersForFile();
            $('#createRackFileModal').modal('show');
        });

        $('#createRackFileForm').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), 'Creating rack file...', function(response) {
                $('#createRackFileModal').modal('hide');
                Swal.fire('Success!', response.message, 'success');
            });
        });
    }

    // Request Physical File - Global event handler using event delegation
    // This ensures it works for dynamically added buttons (like in search results)
    $(document).on('click', '.btn-request-physical-file', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        const fileId = $button.data('file-id');
        const fileName = $button.data('file-name') || 'Unknown File';
        
        // Debug log
        console.log('Request button clicked:', { fileId, fileName, button: $button });
        
        if (!fileId) {
            console.error('File ID is missing!', $button);
            Swal.fire('Error!', 'File ID is missing. Please try again.', 'error');
            return false;
        }
        
        // Set file information in modal
        $('#request_physical_file_id').val(fileId);
        $('#request_physical_file_name').text(fileName);
        
        // Reset form fields
        $('#request_purpose').val('');
        $('#request_urgency').val('normal');
        const charCounter = $('#purpose_char_counter');
        if (charCounter.length) {
            charCounter.html('<span id="purpose_char_count" class="text-muted">0</span>/500 characters <span class="text-danger">(10 more required)</span>');
        } else {
            $('#purpose_char_count').text('0').removeClass('text-danger fw-bold text-success').addClass('text-muted');
        }
        
        // Show modal on top of all other modals
        try {
            // Close any other open modals first
            $('.modal').modal('hide');
            
            setTimeout(function() {
                $('#requestPhysicalFileModal').modal('show');
                
                // Force z-index after modal is shown
                setTimeout(function() {
                    $('#requestPhysicalFileModal').css('z-index', '1070');
                    $('.modal-backdrop').last().css('z-index', '1069');
                }, 100);
                
                // Focus on purpose field after modal is shown
                $('#requestPhysicalFileModal').one('shown.bs.modal', function() {
                    setTimeout(function() {
                        $('#request_purpose').focus();
                    }, 300);
                });
            }, 300);
        } catch (error) {
            console.error('Error showing modal:', error);
            Swal.fire('Error!', 'Could not open request modal. Please try again.', 'error');
        }
        
        return false;
    });

    // Character counter for purpose field
    $(document).on('input', '#request_purpose', function() {
        const length = $(this).val().length;
        const minRequired = 10;
        const charCountSpan = $('#purpose_char_count');
        
        charCountSpan.text(length);
        
        if (length < minRequired) {
            const moreNeeded = minRequired - length;
            charCountSpan.removeClass('text-success').addClass('text-danger fw-bold');
            $('#purpose_char_counter').html(
                '<span id="purpose_char_count" class="text-danger fw-bold">' + length + '</span>/500 characters ' +
                '<span class="text-danger">(' + moreNeeded + ' more required)</span>'
            );
        } else {
            charCountSpan.removeClass('text-danger fw-bold').addClass('text-success');
            $('#purpose_char_counter').html(
                '<span id="purpose_char_count" class="text-success">' + length + '</span>/500 characters ' +
                '<span class="text-muted">(minimum 10 characters required)</span>'
            );
        }
    });

    $('#requestPhysicalFileForm').on('submit', function(e) {
        e.preventDefault();
        
        // Client-side validation
        const form = $(this);
        const purpose = form.find('textarea[name="purpose"]').val().trim();
        const urgency = form.find('select[name="urgency"]').val();
        
        if (!purpose || purpose.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: 'Please provide a purpose with at least <strong>10 characters</strong>.<br><br>' +
                      'Current length: <strong>' + purpose.length + ' characters</strong><br>' +
                      'Required: <strong>10 characters minimum</strong>'
            });
            // Focus on the purpose field
            form.find('textarea[name="purpose"]').focus();
            return false;
        }
        
        if (purpose.length > 500) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Purpose cannot exceed 500 characters. Please shorten your description.'
            });
            form.find('textarea[name="purpose"]').focus();
            return false;
        }
        
        // Urgency validation is handled by HTML5 required attribute, but check anyway
        if (!urgency) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select an urgency level.'
            });
            return false;
        }
        
        submitForm($(this), 'Submitting request...', function(response) {
            $('#requestPhysicalFileModal').modal('hide');
            form[0].reset(); // Reset form after successful submission
            $('#purpose_char_counter').html('<span id="purpose_char_count">0</span>/500 characters (minimum 10 characters required)');
            Swal.fire('Success!', response.message, 'success');
            loadRackFolders(currentRackCategoryId);
        });
    });

    // My Requests for Staff
    if (isStaff) {
        $('#my-requests-btn').on('click', function() {
            loadMyRequests();
        });
    }

    // Pending requests for managers
    if (canManageFiles) {
        $('#rack-pending-requests-btn').on('click', function() {
            Swal.fire({
                title: 'Loading pending requests...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                type: 'POST',
                url: '{{ route("modules.files.physical.ajax") }}',
                data: {
                    action: 'get_pending_rack_requests',
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        displayPendingRackRequests(response.requests);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }
            });
        });
    }

    function displayPendingRackRequests(requests) {
        let html = '';
        if (!Array.isArray(requests) || requests.length === 0) {
            html = '<p class="text-center text-muted">No pending requests.</p>';
        } else {
            html = '<table class="table table-hover"><thead><tr><th>File</th><th>Requester</th><th>Department</th><th>File Status</th><th>Urgency</th><th>Purpose</th><th>Requested</th><th>Actions</th></tr></thead><tbody>';
            requests.forEach(req => {
                const urgencyBadge = req.urgency === 'urgent' ? 'danger' : (req.urgency === 'high' ? 'warning' : (req.urgency === 'normal' ? 'info' : 'secondary'));
                const requesterName = req.requester ? (req.requester.name || (req.requester.first_name && req.requester.last_name ? req.requester.first_name + ' ' + req.requester.last_name : 'Unknown')) : 'Unknown';
                const departmentName = req.requester && req.requester.department ? req.requester.department.name : '-';
                const fileName = req.file ? (req.file.file_name || req.file.original_name || 'Unknown') : 'Unknown';
                const fileNumber = req.file ? (req.file.file_number || '-') : '-';
                const fileStatus = req.file ? req.file.status : 'unknown';
                const fileHolder = req.file && req.file.holder ? req.file.holder.name : (req.file && req.file.current_holder ? 'Someone else' : null);
                const isAvailable = fileStatus === 'available';
                const isIssued = fileStatus === 'issued';
                
                // File status badge
                let fileStatusBadge = '';
                let fileStatusText = '';
                if (fileStatus === 'available') {
                    fileStatusBadge = 'bg-success';
                    fileStatusText = 'Available';
                } else if (fileStatus === 'issued') {
                    fileStatusBadge = 'bg-warning';
                    fileStatusText = `Issued to ${fileHolder || 'Someone'}`;
                } else {
                    fileStatusBadge = 'bg-secondary';
                    fileStatusText = fileStatus.charAt(0).toUpperCase() + fileStatus.slice(1);
                }
                
                // Approval button: enabled only if file is available
                const approveButtonDisabled = !isAvailable;
                const approveButtonClass = approveButtonDisabled ? 'btn-secondary' : 'btn-success';
                const approveButtonTitle = approveButtonDisabled ? 'File is issued. Must be returned first before approval.' : 'Approve Request';
                
                html += `
                    <tr ${isIssued ? 'class="table-warning"' : ''}>
                        <td><strong>${fileName}</strong><br><small class="text-muted">${fileNumber}</small></td>
                        <td>${requesterName}</td>
                        <td>${departmentName}</td>
                        <td>
                            <span class="badge ${fileStatusBadge}">${fileStatusText}</span>
                            ${isIssued ? '<br><small class="text-danger"><strong>File must be returned first</strong></small>' : ''}
                        </td>
                        <td><span class="badge bg-${urgencyBadge}">${req.urgency || 'normal'}</span></td>
                        <td style="max-width:260px">${req.purpose || '-'}</td>
                        <td>${req.requested_at ? new Date(req.requested_at).toLocaleString() : '-'}</td>
                        <td class="d-flex gap-2">
                            <button class="btn btn-sm ${approveButtonClass} btn-approve-rack-request" 
                                    data-request-id="${req.id}" 
                                    data-file-status="${fileStatus}"
                                    ${approveButtonDisabled ? 'disabled' : ''}
                                    title="${approveButtonTitle}">
                                <i class="bx bx-check"></i> ${isAvailable ? 'Approve' : 'Issued'}
                            </button>
                            <button class="btn btn-sm btn-danger btn-reject-rack-request" data-request-id="${req.id}"><i class="bx bx-x"></i> Reject</button>
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
        }
        $('#pendingRackRequestsContent').html(html);
        $('#pendingRackRequestsModal').modal('show');

        $('.btn-approve-rack-request').on('click', function() {
            // Check if button is disabled
            if ($(this).prop('disabled')) {
                return false;
            }
            
            const requestId = $(this).data('request-id');
            const fileStatus = $(this).data('file-status');
            
            // Only allow approval if file status is 'available'
            if (fileStatus !== 'available') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Approve',
                    text: 'This file is currently ' + fileStatus + '. The file must be returned and available before it can be approved to another person.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // File is available - proceed with approval
            $('#approve_request_id').val(requestId);
            $('#approval-notes').val('');
            $('#approveRackRequestModal').modal('show');
            
            // Focus on textarea when modal is shown
            setTimeout(function() {
                $('#approval-notes').focus();
            }, 300);
        });

        $('.btn-reject-rack-request').on('click', function() {
            const requestId = $(this).data('request-id');
            $('#reject_request_id').val(requestId);
            $('#rejection-notes').val('').removeClass('is-invalid');
            $('#rejectRackRequestModal').modal('show');
            
            // Focus on textarea when modal is shown
            setTimeout(function() {
                $('#rejection-notes').focus();
            }, 300);
        });
    }

    function loadMyRequests() {
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'get_my_rack_requests',
                _token: csrfToken
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    displayMyRequests(response.requests);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }

    function displayMyRequests(requests) {
        let html = '';
        
        if (requests.length === 0) {
            html = '<p class="text-center text-muted">You have no file requests.</p>';
        } else {
            html = '<table class="table table-hover"><thead><tr><th>File</th><th>Purpose</th><th>Requested</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            
            requests.forEach(req => {
                const statusColors = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger'
                };
                
                const fileName = req.file ? (req.file.file_name || req.file.original_name || 'Unknown') : 'Unknown';
                const fileNumber = req.file ? (req.file.file_number || '-') : '-';
                const fileId = req.file_id || (req.file ? req.file.id : null);
                const fileStatus = req.file ? req.file.status : null;
                
                html += `
                    <tr>
                        <td><strong>${fileName}</strong><br><small class="text-muted">${fileNumber}</small></td>
                        <td style="max-width: 200px;">${req.purpose || '-'}</td>
                        <td>${req.requested_at ? new Date(req.requested_at).toLocaleDateString() : '-'}</td>
                        <td><span class="badge bg-${statusColors[req.status] || 'secondary'}">${req.status ? req.status.charAt(0).toUpperCase() + req.status.slice(1) : 'Unknown'}</span></td>
                        <td>
                            ${req.status === 'approved' && fileStatus === 'issued' ? `
                                <button class="btn btn-sm btn-success btn-return-file" 
                                        data-file-id="${fileId}"
                                        data-file-name="${fileName}"
                                        data-file-number="${fileNumber}">
                                    Return
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
        }
        
        $('#myRequestsContent').html(html);
        $('#myRequestsModal').modal('show');
        
        $('.btn-return-file').on('click', function() {
            const fileId = $(this).data('file-id');
            const fileName = $(this).data('file-name') || 'Unknown File';
            const fileNumber = $(this).data('file-number') || '-';
            
            $('#return_file_id').val(fileId);
            $('#return_file_name').text(fileName || 'Unknown File');
            $('#return_file_number').text(fileNumber || '-');
            
            // Hide the my requests modal first, then show return modal on top
            $('#myRequestsModal').modal('hide');
            
            // Show return modal on top after a short delay to ensure it appears in front
            setTimeout(function() {
                // Ensure modal backdrop is on top
                $('.modal-backdrop').remove();
                $('#returnFileModal').modal('show');
                
                // Force z-index after modal is shown
                setTimeout(function() {
                    $('#returnFileModal').css('z-index', '1065');
                    $('.modal-backdrop').last().css('z-index', '1064');
                }, 100);
            }, 300);
        });
    }

    // Return File
    $('#returnFileForm').on('submit', function(e) {
        e.preventDefault();
        submitForm($(this), 'Returning file...', function(response) {
            $('#returnFileModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            $('#myRequestsModal').modal('hide');
        });
    });

    // Search functionality
    $('#search-rack-btn').on('click', function() {
        $('#searchRackModal').modal('show');
        // Reset modal search
        $('#modalSearchQuery').val('');
        $('#modalSearchCategory').val('0');
        $('#modalSearchResults').hide();
        $('#clearModalSearchBtn').hide();
    });
    
    // Live search in modal - Search as user types
    let modalSearchTimer = null;
    
    $('#modalSearchQuery').on('input', function() {
        const query = $(this).val().trim();
        const categoryId = $('#modalSearchCategory').val();
        const clearBtn = $('#clearModalSearchBtn');
        
        clearTimeout(modalSearchTimer);
        
        if (query.length > 0) {
            clearBtn.show();
            // Debounce search by 300ms for better performance
            modalSearchTimer = setTimeout(function() {
                performModalLiveSearch(query, categoryId);
            }, 300);
        } else {
            $('#modalSearchResults').hide();
            clearBtn.hide();
        }
    });
    
    // Trigger search when category changes
    $('#modalSearchCategory').on('change', function() {
        const query = $('#modalSearchQuery').val().trim();
        if (query.length > 0) {
            performModalLiveSearch(query, $(this).val());
        }
    });
    
    // Clear modal search
    $('#clearModalSearchBtn').on('click', function() {
        $('#modalSearchQuery').val('');
        $('#modalSearchCategory').val('0');
        $('#modalSearchResults').hide();
        $(this).hide();
    });
    
    function performModalLiveSearch(query, categoryId) {
        if (!query || query.trim().length === 0) {
            $('#modalSearchResults').hide();
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'live_search_rack_files',
                query: query.trim(),
                category_id: categoryId || 0,
                _token: csrfToken
            },
            beforeSend: function() {
                $('#modalSearchResultsContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Searching...</div>');
                $('#modalSearchResults').show();
            },
            success: function(response) {
                if (response.success) {
                    displayModalSearchResults(response.results, query);
                } else {
                    $('#modalSearchResultsContent').html('<div class="alert alert-warning">' + (response.message || 'Search failed') + '</div>');
                }
            },
            error: function() {
                $('#modalSearchResultsContent').html('<div class="alert alert-danger">An error occurred during search.</div>');
            }
        });
    }
    
    function displayModalSearchResults(results, query) {
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bx bx-search"></i> Results: "${query}"</h6>
                <span class="badge bg-info">${results.length} file${results.length !== 1 ? 's' : ''}</span>
            </div>
        `;
        
        if (results.length === 0) {
            html += `
                <div class="text-center py-4">
                    <i class="bx bx-search" style="font-size: 2rem; color: #d0d0d0;"></i>
                    <p class="text-muted mt-2 mb-0">No files found</p>
                    <small class="text-muted">Try adjusting your search terms</small>
                </div>
            `;
        } else {
            html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
            html += '<thead class="table-light"><tr>';
            html += '<th>File Number</th><th>File Name</th><th>Category</th><th>Status</th><th>Action</th>';
            html += '</tr></thead><tbody>';
            
            results.forEach(file => {
                // Handle different possible field names from API
                const fileNumber = file.file_number || file.fileNumber || '-';
                const fileName = file.file_name || file.fileName || file.name || 'Unknown';
                const fileId = file.id || file.file_id;
                const fileStatus = file.status || 'unknown';
                const categoryName = (file.folder && file.folder.category) ? file.folder.category.name : 
                                    (file.category_name || file.categoryName || '-');
                
                // Check if current user is the holder
                const holderId = file.holder ? (file.holder.id || file.current_holder) : (file.current_holder || null);
                const isCurrentHolder = currentUserId && holderId && parseInt(currentUserId) === parseInt(holderId);
                
                const statusBadge = fileStatus === 'available' ? 'bg-success' : 
                                  fileStatus === 'issued' ? 'bg-warning' : 'bg-secondary';
                const statusText = fileStatus === 'available' ? 'Available' : 
                                 fileStatus === 'issued' ? 'Issued' : 'Archived';
                
                // Build action HTML
                let actionHtml = '';
                if (fileStatus === 'available') {
                    actionHtml = `
                        <button class="btn btn-sm btn-outline-primary btn-request-physical-file" 
                                data-file-id="${fileId}" 
                                data-file-name="${fileName}">
                            <i class="bx bx-hand-up"></i> Request
                        </button>
                    `;
                } else if (fileStatus === 'issued' && isCurrentHolder) {
                    // Show Return button if current user is the holder
                    actionHtml = `
                        <button class="btn btn-sm btn-success btn-return-file-from-search" 
                                data-file-id="${fileId}" 
                                data-file-name="${fileName}"
                                data-file-number="${fileNumber}">
                            <i class="bx bx-undo"></i> Return
                        </button>
                    `;
                } else if (fileStatus === 'issued') {
                    actionHtml = '<span class="text-muted small">Issued</span>';
                } else {
                    actionHtml = '<span class="text-muted small">N/A</span>';
                }
                
                html += `
                    <tr>
                        <td><strong class="text-primary">${fileNumber}</strong></td>
                        <td>${fileName}</td>
                        <td>${categoryName}</td>
                        <td><span class="badge ${statusBadge}">${statusText}</span></td>
                        <td>${actionHtml}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        $('#modalSearchResultsContent').html(html);
        $('#modalSearchResults').show();
        
        // Attach event handlers for return buttons from search modal
        $('.btn-return-file-from-search').on('click', function() {
            const fileId = $(this).data('file-id');
            const fileName = $(this).data('file-name') || 'Unknown File';
            const fileNumber = $(this).data('file-number') || '-';
            
            $('#return_file_id').val(fileId);
            $('#return_file_name').text(fileName || 'Unknown File');
            $('#return_file_number').text(fileNumber || '-');
            
            // Close search modal first, then show return modal on top
            $('#searchRackModal').modal('hide');
            
            setTimeout(function() {
                $('#returnFileModal').modal('show');
                setTimeout(function() {
                    $('#returnFileModal').css('z-index', '1065');
                    $('.modal-backdrop').last().css('z-index', '1064');
                }, 100);
            }, 300);
        });
        
        // The global event handler $(document).on('click', '.btn-request-physical-file') will handle clicks
        // But we can verify buttons are set up correctly
        setTimeout(function() {
            $('.btn-request-physical-file').each(function() {
                const fileId = $(this).data('file-id');
                const fileName = $(this).data('file-name');
                if (!fileId) {
                    console.warn('Button missing file-id:', this);
                }
            });
        }, 100);
    }

    // Live search on type - Search files automatically as user types (even with one character)
    let rackSearchTimer = null;
    let isSearchMode = false;
    
    // Search immediately when user starts typing
    $('#rackSearchInput').on('input', function(){
        const term = $(this).val().trim();
        const clearBtn = $('#clearSearchBtn');
        
        // Clear any existing timer
        clearTimeout(rackSearchTimer);
        
        if (term.length > 0) {
            isSearchMode = true;
            clearBtn.show();
            
            // Start search immediately with very short debounce (100ms) for smooth experience
        rackSearchTimer = setTimeout(function(){
                liveSearchRackFiles(term);
            }, 100);
        } else {
            // If search is cleared, reset to folder view
            isSearchMode = false;
            clearBtn.hide();
            loadRackFolders(currentRackCategoryId);
        }
    });
    
    // Also trigger on paste events
    $('#rackSearchInput').on('paste', function(){
        setTimeout(() => {
            $(this).trigger('input');
        }, 10);
    });
    
    $('#clearSearchBtn').on('click', function() {
        $('#rackSearchInput').val('');
        isSearchMode = false;
        $(this).hide();
        loadRackFolders(currentRackCategoryId);
    });
    
    function liveSearchRackFiles(query) {
        // Don't search if query is empty (should not happen, but safety check)
        if (!query || query.trim().length === 0) {
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'live_search_rack_files',
                query: query.trim(),
                _token: csrfToken
            },
            beforeSend: function() {
                $('#rackContents').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Searching...</div>');
            },
            success: function(response) {
                if (response.success) {
                    displayLiveSearchResults(response.results, query);
                } else {
                    $('#rackContents').html('<div class="alert alert-warning">' + (response.message || 'Search failed') + '</div>');
                }
            },
            error: function() {
                $('#rackContents').html('<div class="alert alert-danger">An error occurred during search.</div>');
            }
        });
    }
    
    function displayLiveSearchResults(results, query) {
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6><i class="bx bx-search"></i> Search Results: "${query}"</h6>
                <span class="badge bg-info">${results.length} file${results.length !== 1 ? 's' : ''} found</span>
            </div>
        `;
        
        if (results.length === 0) {
            html += `
                <div class="text-center py-5 border rounded">
                    <i class="bx bx-search" style="font-size: 4rem; color: #d0d0d0;"></i>
                    <h5 class="text-muted mt-3">No files found</h5>
                    <p class="text-muted">Try adjusting your search terms</p>
                </div>
            `;
        } else {
            html += `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>File Number</th>
                                <th>File Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            results.forEach(file => {
                const statusBadge = file.status === 'available' ? 'bg-success' : 
                                  file.status === 'issued' ? 'bg-warning' : 'bg-secondary';
                const statusText = file.status === 'available' ? 'Available' : 
                                 file.status === 'issued' ? 'Issued' : 'Archived';
                const categoryName = (file.folder && file.folder.category) ? file.folder.category.name : '-';
                
                // Check if current user is the holder
                const holderId = file.holder ? (file.holder.id || file.current_holder) : (file.current_holder || null);
                const isCurrentHolder = currentUserId && holderId && parseInt(currentUserId) === parseInt(holderId);
                
                // Build action HTML
                let actionHtml = '';
                if (file.status === 'available') {
                    actionHtml = `
                        <button class="btn btn-sm btn-outline-primary btn-request-physical-file" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name || 'Unknown'}">
                            <i class="bx bx-hand-up"></i> Request
                        </button>
                    `;
                } else if (file.status === 'issued' && isCurrentHolder) {
                    // Show Return button if current user is the holder
                    actionHtml = `
                        <button class="btn btn-sm btn-success btn-return-file-from-list" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name || 'Unknown'}"
                                data-file-number="${file.file_number || '-'}">
                            <i class="bx bx-undo"></i> Return
                        </button>
                    `;
                } else if (file.status === 'issued') {
                    actionHtml = '<span class="text-muted small">Issued</span>';
                } else {
                    actionHtml = '<span class="text-muted small">N/A</span>';
                }
                
                html += `
                    <tr>
                        <td><strong class="text-primary">${file.file_number || '-'}</strong></td>
                        <td>${file.file_name || '-'}</td>
                        <td>${categoryName}</td>
                        <td><span class="badge ${statusBadge}">${statusText}</span></td>
                        <td>${actionHtml}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        $('#rackContents').html(html);
        
        // The global event handler $(document).on('click', '.btn-request-physical-file') will handle clicks
        // But we'll also trigger a custom event to ensure the modal opens properly
        setTimeout(function() {
            // Verify buttons exist and are clickable
            $('.btn-request-physical-file').each(function() {
                if (!$(this).data('click-handler-attached')) {
                    $(this).data('click-handler-attached', true);
                }
            });
        }, 100);
    }

    // Modal form - prevent default submit, live search handles it
    $('#searchRackForm').on('submit', function(e) {
        e.preventDefault();
        // Live search is handled by input event
    });

    function displaySearchResults(results) {
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="bx bx-search"></i> Search Results</h5>
                <span class="badge bg-info">${results.length} file${results.length !== 1 ? 's' : ''} found</span>
            </div>
        `;
        
        if (results.length === 0) {
            html += '<div class="text-center py-5"><i class="bx bx-search" style="font-size: 4rem; color: #d0d0d0;"></i><h5 class="text-muted">No files found</h5></div>';
        } else {
            html += `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>File Number</th>
                                <th>File Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            results.forEach(file => {
                const statusBadge = file.status === 'available' ? 'bg-success' : 
                                  file.status === 'issued' ? 'bg-warning' : 'bg-secondary';
                const statusText = file.status === 'available' ? 'Available' : 
                                 file.status === 'issued' ? 'Issued' : 'Archived';
                const categoryName = (file.folder && file.folder.category) ? file.folder.category.name : '-';
                
                // Check if current user is the holder
                const holderId = file.holder ? (file.holder.id || file.current_holder) : (file.current_holder || null);
                const isCurrentHolder = currentUserId && holderId && parseInt(currentUserId) === parseInt(holderId);
                
                // Build action HTML
                let actionHtml = '';
                if (file.status === 'available') {
                    actionHtml = `
                        <button class="btn btn-sm btn-outline-primary btn-request-physical-file" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name || 'Unknown'}">
                            <i class="bx bx-hand-up"></i> Request
                        </button>
                    `;
                } else if (file.status === 'issued' && isCurrentHolder) {
                    // Show Return button if current user is the holder
                    actionHtml = `
                        <button class="btn btn-sm btn-success btn-return-file-from-list" 
                                data-file-id="${file.id}" 
                                data-file-name="${file.file_name || 'Unknown'}"
                                data-file-number="${file.file_number || '-'}">
                            <i class="bx bx-undo"></i> Return
                        </button>
                    `;
                } else if (file.status === 'issued') {
                    actionHtml = '<span class="text-muted small">Issued</span>';
                } else {
                    actionHtml = '<span class="text-muted small">N/A</span>';
                }
                
                html += `
                    <tr>
                        <td><strong class="text-primary">${file.file_number || '-'}</strong></td>
                        <td>${file.file_name || '-'}</td>
                        <td>${categoryName}</td>
                        <td><span class="badge ${statusBadge}">${statusText}</span></td>
                        <td>${actionHtml}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        $('#rackContents').html(html);
        
        // The global event handler $(document).on('click', '.btn-request-physical-file') will handle clicks
        // Verify buttons are properly set up
        setTimeout(function() {
            $('.btn-request-physical-file').each(function() {
                if (!$(this).data('click-handler-attached')) {
                    $(this).data('click-handler-attached', true);
                }
            });
        }, 100);
    }

    function loadRackFoldersForFile() {
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: {
                action: 'get_rack_folders',
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#rackFolderSelect');
                    select.empty().append('<option value="">Select Rack Folder</option>');
                    response.folders.forEach(folder => {
                        select.append(`<option value="${folder.id}">${folder.rack_number} - ${folder.name}</option>`);
                    });
                }
            }
        });
    }

    // Category Management
    $('#manage-categories-btn').on('click', function() {
        $('#manageCategoriesModal').modal('show');
    });

    $('#add-category-btn').on('click', function() {
        $('#categoryModalTitle').html('<i class="bx bx-category"></i> Add Category');
        $('#categoryAction').val('create_category');
        $('#categoryId').val('');
        $('#categoryName').val('');
        $('#categoryDescription').val('');
        $('#categoryPrefix').val('');
        $('#statusField').hide();
        $('#categoryModal').modal('show');
    });

    $('.edit-category-btn').on('click', function() {
        const categoryId = $(this).data('category-id');
        const row = $(this).closest('tr');
        
        $('#categoryModalTitle').html('<i class="bx bx-category"></i> Edit Category');
        $('#categoryAction').val('update_category');
        $('#categoryId').val(categoryId);
        $('#categoryName').val(row.find('td:eq(0)').text());
        $('#categoryDescription').val(row.find('td:eq(1)').text() === 'N/A' ? '' : row.find('td:eq(1)').text());
        $('#categoryPrefix').val(row.find('td:eq(2) span').text());
        $('#categoryStatus').val(row.find('td:eq(3) span').text().toLowerCase());
        $('#statusField').show();
        $('#categoryModal').modal('show');
    });

    $('.delete-category-btn').on('click', function() {
        const categoryId = $(this).data('category-id');
        const categoryName = $(this).closest('tr').find('td:eq(0)').text();
        
        Swal.fire({
            title: 'Delete Category',
            text: `Are you sure you want to delete "${categoryName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route("modules.files.physical.ajax") }}',
                    data: {
                        _token: csrfToken,
                        action: 'delete_category',
                        category_id: categoryId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            location.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        submitForm($(this), 'Saving category...', function(response) {
            $('#categoryModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            location.reload();
        });
    });

    // Approve Request Form Handler
    $('#approveRackRequestForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#approve_request_id').val();
        const notes = $('#approval-notes').val() || '';
        
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: { 
                _token: csrfToken, 
                action: 'process_rack_request', 
                request_id: requestId, 
                decision: 'approve', 
                notes: notes 
            },
            success: function(resp) {
                if (resp.success) {
                    $('#approveRackRequestModal').modal('hide');
                    Swal.fire('Approved!', resp.message, 'success');
                    $('#pendingRackRequestsModal').modal('hide');
                    loadRackFolders(currentRackCategoryId);
                } else {
                    Swal.fire('Error!', resp.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while approving the request.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Reject Request Form Handler
    $('#rejectRackRequestForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#reject_request_id').val();
        const notes = $('#rejection-notes').val().trim();
        
        // Validate rejection reason
        if (!notes) {
            $('#rejection-notes').addClass('is-invalid');
            return false;
        }
        
        $('#rejection-notes').removeClass('is-invalid');
        
        $.ajax({
            type: 'POST',
            url: '{{ route("modules.files.physical.ajax") }}',
            data: { 
                _token: csrfToken, 
                action: 'process_rack_request', 
                request_id: requestId, 
                decision: 'reject', 
                notes: notes 
            },
            success: function(resp) {
                if (resp.success) {
                    $('#rejectRackRequestModal').modal('hide');
                    Swal.fire('Rejected!', resp.message, 'success');
                    $('#pendingRackRequestsModal').modal('hide');
                    loadRackFolders(currentRackCategoryId);
                } else {
                    Swal.fire('Error!', resp.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while rejecting the request.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Reset modals when closed
    $('#approveRackRequestModal').on('hidden.bs.modal', function() {
        $('#approveRackRequestForm')[0].reset();
        $('#approval-notes').val('');
    });

    $('#rejectRackRequestModal').on('hidden.bs.modal', function() {
        $('#rejectRackRequestForm')[0].reset();
        $('#rejection-notes').val('').removeClass('is-invalid');
    });

    // Initialize
    loadRackFolders(0);
});
</script>
@endpush
