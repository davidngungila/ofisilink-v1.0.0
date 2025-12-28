@extends('layouts.app')

@section('title', 'Advanced Digital File Management')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-folder"></i>Digital File Management
                </h4>
                <p class="text-muted">Enterprise-grade file management with advanced analytics, AI-powered search, and comprehensive audit trails</p>
            </div>
            <div class="btn-group" role="group">
                @if($canManageFiles)
                    <button class="btn btn-primary" id="create-folder-btn">
                        <i class="bx bx-folder-plus"></i> Create Folder
                    </button>
                    <button class="btn btn-primary" id="bulk-create-folders-btn">
                        <i class="bx bx-folder"></i> Bulk Create Folders
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ asset('assets/templates/bulk_folders_template.csv') }}" download>
                        <i class="bx bx-download"></i> Download Template
                    </a>
                    <button class="btn btn-success" id="upload-file-btn">
                        <i class="bx bx-upload"></i> Upload File
                    </button>
                    <button class="btn btn-info" id="bulk-upload-btn">
                        <i class="bx bx-cloud-upload"></i> Bulk Upload
                    </button>
                @endif
                <button class="btn btn-primary" id="live-search-btn">
                    <i class="bx bx-search-alt"></i> Live Search
                </button>
                <button class="btn btn-secondary" id="analytics-btn">
                    <i class="bx bx-bar-chart"></i> Analytics
                </button>
                <button class="btn btn-outline-dark" id="refresh-btn">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.css">
<style>
    .file-card { 
        transition: all 0.3s ease; 
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }
    .file-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
        border-color: #007bff;
    }
    .file-upload-area { 
        border: 2px dashed #dee2e6; 
        transition: all 0.3s; 
        border-radius: 8px;
    }
    .file-upload-area.dragover { 
        border-color: #007bff; 
        background-color: #f8f9ff; 
    }
    .folder-tree {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
    }
    .folder-item {
        padding: 8px 12px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .folder-item:hover {
        background-color: #e9ecef;
    }
    .folder-item.active {
        background-color: #007bff;
        color: white;
    }
    .file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        padding: 15px 0;
    }
    .file-item {
        background: white;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .file-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .folder-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        z-index: 10;
        background-color: white;
        border: 2px solid #007bff;
    }
    .folder-checkbox:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    .file-item:has(.folder-checkbox:checked) {
        border: 2px solid #007bff;
        background-color: #f0f8ff;
    }
    .file-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    .file-name {
        font-weight: 500;
        margin-bottom: 5px;
        word-break: break-word;
    }
    .file-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .activity-timeline {
        position: relative;
        padding-left: 30px;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .activity-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 20px;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
    }
    .search-filters {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .dropzone {
        border: 2px dashed #007bff;
        border-radius: 8px;
        background: #f8f9ff;
        padding: 40px;
        text-align: center;
        transition: all 0.3s;
        min-height: 200px;
        position: relative;
    }
    .dropzone.dz-drag-hover {
        border-color: #28a745;
        background: #f0fff4;
        transform: scale(1.02);
    }
    .dropzone .dz-message {
        margin: 0;
    }
    .dropzone .dz-preview {
        margin: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dropzone .dz-preview .dz-image {
        width: 80px;
        height: 80px;
        border-radius: 4px;
    }
    .dropzone .dz-progress {
        width: 100%;
        height: 6px;
        margin-top: 10px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    .dropzone .dz-progress .dz-upload {
        height: 100%;
        background: #007bff;
        transition: width 0.3s;
    }
    .dropzone .dz-preview.dz-success .dz-progress .dz-upload {
        background: #28a745;
    }
    .dropzone .dz-preview.dz-error .dz-progress .dz-upload {
        background: #dc3545;
    }
    .progress-container {
        margin-top: 10px;
    }
    .file-preview {
        max-width: 100px;
        max-height: 100px;
        border-radius: 4px;
    }
    .tag-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }
    .tag {
        background: #e9ecef;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        color: #495057;
    }
    .access-level-badge {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
    .confidentiality-badge {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
    .file-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }
    .file-item:hover .file-actions {
        opacity: 1;
    }
    .folder-actions-dropdown {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1050;
    }
    .folder-item:hover .folder-actions-dropdown {
        opacity: 1;
    }
    .folder-actions-menu {
        min-width: 200px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
        z-index: 1051 !important;
        position: absolute !important;
    }
    .folder-item {
        position: relative;
        z-index: 1;
    }
    .folder-item:hover {
        z-index: 1050;
    }
    .file-grid .folder-item:hover {
        z-index: 1050;
    }
    .dropdown-menu.folder-actions-menu {
        z-index: 1051 !important;
    }
    .folder-actions-menu .dropdown-item {
        padding: 10px 15px;
        cursor: pointer;
    }
    .folder-actions-menu .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .folder-actions-menu .dropdown-item i {
        width: 20px;
        margin-right: 8px;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    .breadcrumb-nav {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 20px;
    }
    .view-toggle {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
    }
    .view-toggle button {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .view-toggle button.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    /* Ensure SweetAlert is above Bootstrap modals */
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Files</h6>
                        <h3 class="mb-0 text-primary">{{ $stats['total_files'] ?? 0 }}</h3>
                        <small class="text-success">
                            <i class="bx bx-trending-up"></i> +{{ $stats['files_this_month'] ?? 0 }} this month
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-file"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Folders</h6>
                        <h3 class="mb-0 text-success">{{ $stats['total_folders'] ?? 0 }}</h3>
                        <small class="text-info">
                            <i class="bx bx-folder"></i> {{ $stats['public_folders'] ?? 0 }} public
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-folder"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Storage Used</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['storage_used'] ?? '0 MB' }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-hdd"></i> {{ $stats['storage_percentage'] ?? 0 }}% of quota
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-hdd"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending Requests</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['pending_requests'] ?? 0 }}</h3>
                        <small class="text-warning">
                            <i class="bx bx-time"></i> {{ $stats['my_pending_requests'] ?? 0 }} my requests
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-danger rounded">
                            <i class="bx bx-time"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Sidebar - Folder Tree -->
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-folder-tree"></i> Folder Structure
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="folder-tree" id="folderTree">
                        <!-- Folder tree will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-zap"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($canManageFiles)
                            <button class="btn btn-outline-primary btn-sm" id="quick-create-folder">
                                <i class="bx bx-folder-plus"></i> New Folder
                            </button>
                            <button class="btn btn-outline-success btn-sm" id="quick-upload">
                                <i class="bx bx-upload"></i> Upload File
                            </button>
                        @endif
                        <button class="btn btn-outline-info btn-sm" id="quick-search">
                            <i class="bx bx-search"></i> Search Files
                        </button>
                        <button class="btn btn-outline-warning btn-sm" id="request-access">
                            <i class="bx bx-key"></i> Request Access
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history"></i> Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="recent-activity" id="recentActivity">
                        <!-- Recent activity will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Breadcrumb Navigation -->
            <div class="breadcrumb-nav" id="breadcrumbNav">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="#" onclick="loadFolderContents(0)" class="text-decoration-none">
                                <i class="bx bx-home"></i> Root
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Filters and Bulk Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="sortBy">
                        <option value="name">Sort by Name</option>
                        <option value="date">Sort by Date</option>
                        <option value="size">Sort by Size</option>
                        <option value="type">Sort by Type</option>
                    </select>
                    <select class="form-select form-select-sm" id="filterBy">
                        <option value="all">All Files</option>
                        <option value="documents">Documents</option>
                        <option value="images">Images</option>
                        <option value="videos">Videos</option>
                        <option value="archives">Archives</option>
                    </select>
                </div>
                @if($canManageFiles)
                <div class="d-flex gap-2 align-items-center" id="bulkActionsToolbar" style="display: none;">
                    <span class="text-muted" id="selectedCount">0 selected</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-check-double"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="bulkDeleteFolders(); return false;"><i class="bx bx-trash text-danger"></i> Delete Selected</a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkMoveFolders(); return false;"><i class="bx bx-move"></i> Move Selected</a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkAssignFolders(); return false;"><i class="bx bx-user"></i> Assign to Staff</a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkAssignFoldersToDept(); return false;"><i class="bx bx-building"></i> Assign to Department</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="selectAllFolders(); return false;"><i class="bx bx-check-square"></i> Select All</a></li>
                            <li><a class="dropdown-item" href="#" onclick="deselectAllFolders(); return false;"><i class="bx bx-square"></i> Deselect All</a></li>
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <!-- File Content Area -->
            <div class="card">
                <div class="card-body">
                    <div id="fileContent">
                        <!-- Files and folders will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-folder-plus"></i> Create New Folder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFolderForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="create_folder">
                    <input type="hidden" name="parent_id" id="folderParentId" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Name *</label>
                        <input type="text" name="folder_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Code</label>
                        <input type="text" name="folder_code" class="form-control" placeholder="e.g., HR-DOCS-2024">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Level *</label>
                        <select name="access_level" class="form-select" required>
                            <option value="public">Public - All users can access</option>
                            <option value="department">Department - Only department members</option>
                            <option value="private">Private - Assigned users only</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="departmentField" style="display: none;">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
                    </form>
                </div>
            </div>
        </div>

<!-- Bulk Create Folders Modal -->
<div class="modal fade" id="bulkCreateFoldersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-folder"></i> Bulk Create Folders from Excel/CSV</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkCreateFoldersForm" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="bulk_create_folders">
                    <input type="hidden" name="parent_id" id="bulkCreateFoldersParentId" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Folder *</label>
                        <div class="border rounded p-3 bg-light" id="bulkCreateFoldersParentName">
                            <i class="bx bx-home"></i> <strong>Root (Top Level)</strong>
                        </div>
                        <small class="form-text text-muted"><strong>Important:</strong> All folders from your Excel file will be created as children of this parent folder. The "Parent Folder ID" column in your Excel will be ignored.</small>
                    </div>
                    
                    <div class="alert alert-warning" id="bulkCreateFoldersParentWarning" style="display: none;">
                        <i class="bx bx-info-circle"></i> <strong>Note:</strong> Since you're creating folders within a specific folder, the "Parent Folder ID" column in your Excel file will be ignored. All folders will be created as children of the selected parent folder above.
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bx bx-info-circle"></i> Excel/CSV Format:</h6>
                        <p class="mb-2">Your Excel/CSV file should have the following columns (in order):</p>
                        <ol class="mb-0">
                            <li><strong>Folder Name</strong> (Required)</li>
                            <li><strong>Description</strong> (Optional)</li>
                            <li><strong>Folder Code</strong> (Optional)</li>
                            <li><strong>Parent Folder ID</strong> (Optional - will be ignored if creating within a folder)</li>
                            <li><strong>Access Level</strong> (Optional: public, department, private)</li>
                            <li><strong>Department ID</strong> (Optional, required if access_level is department)</li>
                        </ol>
                        <hr>
                        <p class="mb-0"><strong>Note:</strong> The first row should be headers. Supported formats: .xlsx, .xls, .csv</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload Excel/CSV File *</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">Maximum file size: 10MB</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle"></i> <strong>Tip:</strong> Export your Excel file as CSV (UTF-8) for best compatibility.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload"></i> Process and Create Folders
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Folder Modal -->
<div class="modal fade" id="editFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bx bx-edit"></i> Edit Folder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFolderForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="update_folder">
                    <input type="hidden" name="folder_id" id="editFolderId">
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Name *</label>
                        <input type="text" name="folder_name" id="editFolderName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editFolderDescription" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Code</label>
                        <input type="text" name="folder_code" id="editFolderCode" class="form-control" placeholder="e.g., HR-DOCS-2024">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Level *</label>
                        <select name="access_level" id="editAccessLevel" class="form-select" required>
                            <option value="public">Public - All users can access</option>
                            <option value="department">Department - Only department members</option>
                            <option value="private">Private - Assigned users only</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="editDepartmentField" style="display: none;">
                        <label class="form-label">Department</label>
                        <select name="department_id" id="editDepartmentId" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Folder to Staff Modal -->
<div class="modal fade" id="assignFolderToStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-user"></i> Assign Folder to Staff</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignFolderToStaffForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="assign_folder_to_staff">
                    <input type="hidden" name="folder_id" id="assignFolderStaffId">
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <p class="form-control-plaintext" id="assignFolderStaffName"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Staff Member *</label>
                        <select name="user_id" id="assignStaffSelect" class="form-select" required>
                            <option value="">Select Staff Member</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Folder to Department Modal -->
<div class="modal fade" id="assignFolderToDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-building"></i> Assign Folder to Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignFolderToDepartmentForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="assign_folder_to_department">
                    <input type="hidden" name="folder_id" id="assignFolderDeptId">
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <p class="form-control-plaintext" id="assignFolderDeptName"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Department *</label>
                        <select name="department_id" id="assignDepartmentSelect" class="form-select" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-upload"></i> Upload File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadFileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="upload_file">
                    <input type="hidden" name="folder_id" id="uploadFolderId" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Folder *</label>
                        <select name="folder_id" class="form-select" required>
                            <option value="">Select Folder</option>
                            <!-- Folders will be populated dynamically -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File *</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.odt,.ods,.odp,.csv,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.tiff,.tif,.ico,.heic,.heif" required>
                        <small class="form-text text-muted">Maximum file size: 20MB. Only documents and images are accepted.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Access Level *</label>
                                <select name="access_level" id="uploadAccessLevel" class="form-select" required>
                                    <option value="public">Public</option>
                                    <option value="department">Department</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confidentiality Level *</label>
                                <select name="confidential_level" class="form-select" required>
                                    <option value="normal">Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="strictly_confidential">Strictly Confidential</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="uploadDepartmentField" style="display: none;">
                        <label class="form-label">Department *</label>
                        <select name="department_id" id="uploadDepartmentId" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Required when access level is Department</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="Enter tags separated by commas">
                        <small class="form-text text-muted">e.g., important, project, meeting</small>
                    </div>
                    
                    <div class="mb-3" id="assignedUsersField" style="display: none;">
                        <label class="form-label">Assign to Users</label>
                        <select name="assigned_users[]" class="form-select" multiple>
                            <!-- Users will be populated dynamically -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-cloud-upload"></i> Bulk Upload Files</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkUploadForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="action" value="bulk_upload">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Folder *</label>
                        <select name="folder_id" class="form-select" required>
                            <option value="">Select Folder</option>
                            <!-- Folders will be populated dynamically -->
                        </select>
                    </div>
                    
                    <div class="dropzone" id="bulkDropzone">
                        <div class="dz-message">
                            <i class="bx bx-cloud-upload" style="font-size: 3rem; color: #007bff;"></i>
                            <h4>Drop documents and images here or click to upload</h4>
                            <p class="text-muted">You can upload multiple files at once</p>
                            <p class="text-muted small mt-2">
                                <strong>Accepted formats:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, RTF, CSV, ODT, ODS, ODP<br>
                                <strong>Images:</strong> JPG, JPEG, PNG, GIF, BMP, WEBP, SVG, TIFF, ICO, HEIC, HEIF
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Access Level *</label>
                                <select name="access_level" id="bulkAccessLevel" class="form-select" required>
                                    <option value="public">Public</option>
                                    <option value="department">Department</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confidentiality Level *</label>
                                <select name="confidential_level" class="form-select" required>
                                    <option value="normal">Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="strictly_confidential">Strictly Confidential</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="bulkDepartmentField" style="display: none;">
                        <label class="form-label">Department *</label>
                        <select name="department_id" id="bulkDepartmentId" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Required when access level is Department</small>
                    </div>
                    
                    <div class="mb-3" id="bulkAssignedUsersField" style="display: none;">
                        <label class="form-label">Assign to Users *</label>
                        <select name="assigned_users[]" id="bulkAssignedUsers" class="form-select" multiple style="min-height: 100px;">
                            <!-- Users will be populated dynamically -->
                        </select>
                        <small class="form-text text-muted">Select users who should have access to these files</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="Enter tags separated by commas">
                        <small class="form-text text-muted">e.g., important, project, meeting</small>
                    </div>
                    
                    <div class="alert alert-info mt-3" id="bulkUploadInfo" style="display: none;">
                        <i class="bx bx-info-circle"></i> <strong id="bulkFileCount">0</strong> file(s) selected for upload
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="startBulkUpload">Start Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- Live Search Modal -->
<div class="modal fade" id="liveSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-search-alt"></i> Live Search</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="liveSearchInput" class="form-control form-control-lg" placeholder="Type to search folders and files...">
                </div>
                <div id="liveSearchResults" class="mt-3">
                    <div class="text-muted">Start typing to search folders and files...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Search Modal -->
<div class="modal fade" id="advancedSearchModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bx bx-search-alt"></i> Advanced Search</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="advancedSearchForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="advanced_search">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Search Term</label>
                                <input type="text" name="search_term" class="form-control" placeholder="Enter file name or content">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">File Type</label>
                                <select name="file_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="pdf">PDF Documents</option>
                                    <option value="image">Images</option>
                                    <option value="document">Word Documents</option>
                                    <option value="spreadsheet">Spreadsheets</option>
                                    <option value="presentation">Presentations</option>
                                    <option value="archive">Archives</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Folder</label>
                                <select name="folder_id" class="form-select">
                                    <option value="">All Folders</option>
                                    <!-- Folders will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Access Level</label>
                                <select name="access_level" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="public">Public</option>
                                    <option value="department">Department</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confidentiality Level</label>
                                <select name="confidential_level" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="normal">Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="strictly_confidential">Strictly Confidential</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="Enter tags separated by commas">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Size Range</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="number" name="size_from" class="form-control" placeholder="Min size (KB)">
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="size_to" class="form-control" placeholder="Max size (KB)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-bar-chart"></i> File Analytics</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <h6 class="text-center mb-3"><i class="bx bx-pie-chart"></i> File Types Distribution</h6>
                            <canvas id="fileTypeChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <h6 class="text-center mb-3"><i class="bx bx-bar-chart"></i> Storage by Department</h6>
                            <canvas id="storageChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <h6 class="text-center mb-3"><i class="bx bx-line-chart"></i> Activity Timeline (Last 30 Days)</h6>
                            <canvas id="activityChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <h6 class="text-center mb-3"><i class="bx bx-bar-chart-alt"></i> Files by Department</h6>
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Details Modal -->
<div class="modal fade" id="fileDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-file"></i> File Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fileDetailsContent">
                <!-- File details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadFileBtn">Download</button>
                <button type="button" class="btn btn-success" id="requestAccessBtn">Request Access</button>
            </div>
        </div>
    </div>
</div>

<!-- Request Access Modal -->
<div class="modal fade" id="requestAccessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-key"></i> Request File Access</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="requestAccessForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="request_file_access">
                    <input type="hidden" name="file_id" id="requestFileId">
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose for Access *</label>
                        <textarea name="purpose" id="requestAccessPurpose" class="form-control" rows="4" required minlength="10" maxlength="500" placeholder="Please explain why you need access to this file (minimum 10 characters)..."></textarea>
                        <small class="text-muted" id="purpose_access_char_counter">
                            <span id="purpose_access_char_count">0</span>/500 characters (minimum 10 characters required)
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urgency Level *</label>
                        <select name="urgency" class="form-select" required>
                            <option value="">Select Urgency</option>
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Duration</label>
                        <select name="duration" class="form-select">
                            <option value="1">1 Day</option>
                            <option value="7" selected>1 Week</option>
                            <option value="30">1 Month</option>
                            <option value="90">3 Months</option>
                            <option value="365">1 Year</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Request</button>
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
                <h5 class="modal-title"><i class="bx bx-clipboard"></i> My File Access Requests</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                    <div class="table-responsive">
                    <table class="table table-striped" id="myRequestsTable">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                            <tbody>
                            <!-- Requests will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Requests Modal -->
<div class="modal fade" id="pendingRequestsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="bx bx-time"></i> Pending File Access Requests</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="pendingRequestsTable">
                        <thead>
                            <tr>
                                <th>Requester</th>
                                <th>File Name</th>
                                <th>Reason</th>
                                <th>Urgency</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Pending requests will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
$(document).ready(function() {
    // Constants and Configuration
    const csrfToken = '{{ csrf_token() }}';
    const canManageFiles = {{ $canManageFiles ? 'true' : 'false' }};
    const currentUserId = {{ Auth::id() }};
    const isStaff = {{ $isStaff ? 'true' : 'false' }};
    const currentUserDeptId = {{ Auth::user()->primary_department_id ?? 'null' }};
    
    let currentFolderId = 0;
    let currentView = 'grid';
    let folderTreeData = [];
    let fileTypeChart = null;
    let storageChart = null;
    let activityChart = null;
    let departmentChart = null;

    // Initialize Application
    function initApplication() {
        loadFolderTree();
        loadFolderContents(0);
        updateDashboardStats();
        setupEventListeners();
        initializeDragAndDrop();
        loadRecentActivity();
        initializeCharts();
        setupLiveSearch();
    }

    // Setup Event Listeners
    function setupEventListeners() {
        // Always use grid view
        currentView = 'grid';

        // Sort and Filter
        $('#sortBy, #filterBy').on('change', function() {
            loadFolderContents(currentFolderId);
        });

        // Modal Events
        $('#create-folder-btn, #quick-create-folder').on('click', function() {
            $('#folderParentId').val(currentFolderId);
            // Reset modal title
            $('#createFolderModal .modal-title').html('<i class="bx bx-folder-plus"></i> Create New Folder');
            $('#createFolderModal').modal('show');
        });

        $('#bulk-create-folders-btn').on('click', function() {
            $('#bulkCreateFoldersParentId').val(0);
            $('#bulkCreateFoldersParentName').html('<i class="bx bx-home"></i> <strong>Root (Top Level)</strong>');
            $('#bulkCreateFoldersParentWarning').hide();
            $('#bulkCreateFoldersModal').modal('show');
        });

        $('#upload-file-btn, #quick-upload').on('click', function() {
            $('#uploadFolderId').val(currentFolderId);
            populateFolderSelect('#uploadFileForm select[name="folder_id"]');
            // Reset access level and hide conditional fields
            $('#uploadAccessLevel').val('public');
            $('#uploadDepartmentField').hide();
            $('#uploadDepartmentId').prop('required', false).val('');
            $('#assignedUsersField').hide();
            $('#uploadFileModal').modal('show');
        });

        $('#bulk-upload-btn').on('click', function() {
            populateFolderSelect('#bulkUploadForm select[name="folder_id"]');
            // Reset access level and hide conditional fields
            $('#bulkAccessLevel').val('public');
            $('#bulkDepartmentField').hide();
            $('#bulkDepartmentId').prop('required', false).val('');
            $('#bulkAssignedUsersField').hide();
            $('#bulkAssignedUsers').prop('required', false).val(null).trigger('change');
            loadUsersForBulkAssignment();
            // Reset dropzone initialization flag when modal opens
            dropzoneInitialized = false;
            initializeDropzone();
            $('#bulkUploadModal').modal('show');
        });
        
        // Access level change for bulk upload
        $(document).on('change', '#bulkAccessLevel', function() {
            const accessLevel = $(this).val();
            const departmentField = $('#bulkDepartmentField');
            const assignedUsersField = $('#bulkAssignedUsersField');
            const departmentSelect = $('#bulkDepartmentId');
            const assignedUsersSelect = $('#bulkAssignedUsers');
            
            if (accessLevel === 'department') {
                departmentField.show();
                departmentSelect.prop('required', true);
                assignedUsersField.hide();
                assignedUsersSelect.prop('required', false).val(null).trigger('change');
            } else if (accessLevel === 'private') {
                departmentField.hide();
                departmentSelect.prop('required', false).val('');
                assignedUsersField.show();
                assignedUsersSelect.prop('required', true);
            } else {
                departmentField.hide();
                departmentSelect.prop('required', false).val('');
                assignedUsersField.hide();
                assignedUsersSelect.prop('required', false).val(null).trigger('change');
            }
        });

        $('#live-search-btn, #quick-search').on('click', function() {
            $('#liveSearchInput').val('');
            $('#liveSearchResults').html('<div class="text-muted">Start typing to search folders and files...</div>');
            $('#liveSearchModal').modal('show');
            setTimeout(() => { $('#liveSearchInput').trigger('focus'); }, 200);
        });

        $('#analytics-btn').on('click', function() {
            loadAnalytics();
            $('#analyticsModal').modal('show');
        });

        $('#my-requests-btn').on('click', function() {
            loadMyRequests();
            $('#myRequestsModal').modal('show');
        });

        $('#pending-requests-btn').on('click', function() {
            loadPendingRequests();
            $('#pendingRequestsModal').modal('show');
        });

        $('#request-access').on('click', function() {
            $('#requestAccessModal').modal('show');
        });

        $('#refresh-btn').on('click', function() {
            loadFolderContents(currentFolderId);
            loadRecentActivity();
        });

        // Form Submissions
        $('#createFolderForm').on('submit', handleCreateFolder);
        $('#bulkCreateFoldersForm').on('submit', handleBulkCreateFolders);
        $('#editFolderForm').on('submit', handleUpdateFolder);
        $('#assignFolderToStaffForm').on('submit', handleAssignFolderToStaff);
        $('#assignFolderToDepartmentForm').on('submit', handleAssignFolderToDepartment);
        $('#uploadFileForm').on('submit', handleUploadFile);
        $('#bulkUploadForm').on('submit', handleBulkUpload);
        $('#advancedSearchForm').on('submit', handleAdvancedSearch);
        $('#requestAccessForm').on('submit', handleRequestAccess);
        
        // Access level change for edit folder modal
        $('#editAccessLevel').on('change', function() {
            const accessLevel = $(this).val();
            const departmentField = $('#editDepartmentField');
            
            if (accessLevel === 'department') {
                departmentField.show();
            } else {
                departmentField.hide();
            }
        });

        // Access Level Change for Upload File Modal
        $('#uploadAccessLevel').on('change', function() {
            const accessLevel = $(this).val();
            const departmentField = $('#uploadDepartmentField');
            const departmentSelect = $('#uploadDepartmentId');
            const assignedUsersField = $('#assignedUsersField');
            
            if (accessLevel === 'department') {
                departmentField.show();
                departmentSelect.prop('required', true);
            } else {
                departmentField.hide();
                departmentSelect.prop('required', false);
                departmentSelect.val(''); // Clear selection
            }
            
            if (accessLevel === 'private') {
                assignedUsersField.show();
                loadUsersForAssignment();
            } else {
                assignedUsersField.hide();
            }
        });

        // Access Level Change (Generic handler for other modals)
        $('select[name="access_level"]').not('#uploadAccessLevel').not('#editAccessLevel').on('change', function() {
            const accessLevel = $(this).val();
            const departmentField = $(this).closest('.modal').find('#departmentField');
            const assignedUsersField = $(this).closest('.modal').find('#assignedUsersField');
            
            if (accessLevel === 'department') {
                departmentField.show();
            } else {
                departmentField.hide();
            }
            
            if (accessLevel === 'private') {
                assignedUsersField.show();
                loadUsersForAssignment();
            } else {
                assignedUsersField.hide();
            }
        });
    }

    // Live Search (single input, debounce, search_all)
    let liveSearchTimer = null;
    function setupLiveSearch() {
        $('#liveSearchInput').on('keyup', function() {
            const term = $(this).val().trim();
            clearTimeout(liveSearchTimer);
            liveSearchTimer = setTimeout(function(){
                if (term.length === 0) {
                    $('#liveSearchResults').html('<div class="text-muted">Start typing to search folders and files...</div>');
                    return;
                }
                $('#liveSearchResults').html('<div class="text-muted">Searching...</div>');
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: { _token: csrfToken, action: 'search_all', search_term: term },
                    success: function(response) {
                        if (response.success) {
                            renderLiveSearchResults(response.results);
                        } else {
                            $('#liveSearchResults').html('<div class="text-danger">'+ (response.message || 'Search failed') +'</div>');
                        }
                    },
                    error: function() {
                        $('#liveSearchResults').html('<div class="text-danger">Search failed</div>');
                    }
                });
            }, 300);
        });
    }

    function renderLiveSearchResults(results) {
        const folders = results.folders || [];
        const files = results.files || [];
        let html = '';
        
        if (folders.length === 0 && files.length === 0) {
            $('#liveSearchResults').html('<div class="text-muted">No results found</div>');
            return;
        }
        
        if (folders.length > 0) {
            html += '<h6 class="mt-2">Folders</h6><div class="list-group mb-3">';
            folders.forEach(f => {
                html += `<a href="#" class="list-group-item list-group-item-action" onclick="loadFolderContents(${f.id}); $('#liveSearchModal').modal('hide'); return false;">
                    <i class="bx bx-folder text-primary me-2"></i>${f.name}
                </a>`;
            });
            html += '</div>';
        }
        
        if (files.length > 0) {
            html += '<h6 class="mt-2">Files</h6><div class="list-group">';
            files.forEach(file => {
                html += `<a href="#" class="list-group-item list-group-item-action" onclick="viewFileDetails(${file.id}); return false;">
                    <i class="bx ${getFileIcon(file.mime_type)} text-info me-2"></i>${file.original_name}
                    <small class="text-muted ms-2">${formatFileSize(file.file_size || 0)}</small>
                </a>`;
            });
            html += '</div>';
        }
        
        $('#liveSearchResults').html(html);
    }

    // Load Folder Tree
    function loadFolderTree() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_folder_tree'
            },
            success: function(response) {
                if (response.success) {
                    folderTreeData = response.tree;
                    renderFolderTree(response.tree);
                }
            }
        });
    }

    // Render Folder Tree
    function renderFolderTree(tree, parentId = null, level = 0) {
        const container = parentId ? $(`#folder-${parentId}`) : $('#folderTree');
        
        if (level === 0) {
            container.empty();
        }

        // Filter folders correctly - handle null parent_id for root folders
        const folders = tree.filter(folder => {
            if (parentId === null) {
                return folder.parent_id === null || folder.parent_id === 0;
            } else {
                return folder.parent_id === parentId;
            }
        });
        
        if (folders.length === 0 && level === 0) {
            container.html('<div class="text-muted p-3"><i class="bx bx-info-circle"></i> No folders available</div>');
            return;
        }
        
        folders.forEach(folder => {
            const totalFiles = folder.files_count || 0;
            const folderHtml = `
                <div class="folder-item" data-folder-id="${folder.id}" style="padding-left: ${level * 20}px">
                    <i class="bx bx-folder"></i>
                    <span class="ms-2">${folder.name}</span>
                    <span class="badge bg-secondary ms-2" title="Total files including subfolders">${totalFiles}</span>
                </div>
                <div id="folder-${folder.id}" style="display: none;"></div>
            `;
            
            container.append(folderHtml);
            
            // Add click handler
            $(`.folder-item[data-folder-id="${folder.id}"]`).on('click', function(e) {
                e.stopPropagation();
                loadFolderContents(folder.id);
                updateBreadcrumb(folder);
                
                // Update active state
                $('.folder-item').removeClass('active');
                $(this).addClass('active');
            });
            
            // Render subfolders if they exist
            if (folder.subfolders && folder.subfolders.length > 0) {
                renderFolderTree(folder.subfolders, folder.id, level + 1);
            } else {
                // Also check the flat tree structure for subfolders
                const subfolders = tree.filter(f => f.parent_id === folder.id);
                if (subfolders.length > 0) {
            renderFolderTree(tree, folder.id, level + 1);
                }
            }
        });
    }

    // Load Folder Contents
    function loadFolderContents(folderId) {
        currentFolderId = folderId;
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_folder_contents',
                folder_id: folderId,
                view: currentView,
                sort_by: $('#sortBy').val(),
                filter_by: $('#filterBy').val()
            },
            success: function(response) {
                if (response.success) {
                    renderFolderContents(response.contents);
                }
            }
        });
    }

    // Render Folder Contents
    function renderFolderContents(contents) {
        const container = $('#fileContent');
        container.empty();
            renderGridView(contents);
    }

    // Render Grid View
    function renderGridView(contents) {
        const container = $('#fileContent');
        container.html('<div class="file-grid"></div>');
        const grid = container.find('.file-grid');

        // Render folders
        contents.folders.forEach(folder => {
            const canManage = {{ $canManageFiles ? 'true' : 'false' }};
            const folderNameEscaped = escapeHtml(folder.name);
            const folderDescriptionEscaped = escapeHtml(folder.description || '');
            const folderCodeEscaped = escapeHtml(folder.folder_code || '');
            
            const folderHtml = `
                <div class="file-item folder-item position-relative" data-folder-id="${folder.id}" onclick="loadFolderContents(${folder.id}); event.stopPropagation();">
                    ${canManage ? `
                    <div class="position-absolute top-0 start-0 m-2" onclick="event.stopPropagation();">
                        <input type="checkbox" class="form-check-input folder-checkbox" value="${folder.id}" id="folder-check-${folder.id}" onchange="updateBulkActionsToolbar();">
                    </div>
                    ` : ''}
                    <div class="file-icon text-primary">
                        <i class="bx bx-folder" style="font-size: 3rem;"></i>
                    </div>
                    <div class="file-name">${folderNameEscaped}</div>
                    <div class="file-meta">
                        <span class="badge bg-primary access-level-badge">${folder.access_level}</span>
                        <br>
                        <small>${folder.files_count || 0} files</small>
                    </div>
                    ${canManage ? `
                    <div class="folder-actions-dropdown">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();" aria-expanded="false" data-bs-auto-close="true">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu folder-actions-menu dropdown-menu-end" onclick="event.stopPropagation();" style="z-index: 1051 !important; position: absolute !important;">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="createFolderInFolder(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-folder-plus"></i> Create Folder Here
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="bulkCreateFoldersInFolder(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-folder"></i> Bulk Create Folders Here
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="assignFolderToStaff(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-user"></i> Assign to Staff
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="assignFolderToDepartment(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-building"></i> Assign to Department
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="editFolder(${folder.id}, '${folderNameEscaped}', '${folderDescriptionEscaped}', '${folderCodeEscaped}', '${folder.access_level}', ${folder.department_id || 'null'}); return false;">
                                        <i class="bx bx-edit"></i> Edit Folder
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteFolder(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-trash"></i> Delete Folder
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="moveFolder(${folder.id}, '${folderNameEscaped}'); return false;">
                                        <i class="bx bx-move"></i> Move Folder
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="viewFolderDetails(${folder.id}); return false;">
                                        <i class="bx bx-info-circle"></i> View Details
                                    </a>
                                </li>
                            </ul>
                    </div>
                    </div>
                    ` : ''}
                </div>
            `;
            grid.append(folderHtml);
        });

        // Render files
        contents.files.forEach(file => {
            const fileIcon = getFileIcon(file.mime_type);
            const fileSize = formatFileSize(file.file_size);
            const tags = file.tags ? file.tags.split(',').map(tag => `<span class="tag">${tag.trim()}</span>`).join('') : '';
            
            const fileHtml = `
                <div class="file-item" data-file-id="${file.id}">
                    <div class="file-icon text-info">
                        <i class="${fileIcon}"></i>
                    </div>
                    <div class="file-name">${file.original_name}</div>
                    <div class="file-meta">
                        <span class="badge bg-${getAccessLevelColor(file.access_level)} access-level-badge">${file.access_level}</span>
                        <span class="badge bg-${getConfidentialityColor(file.confidential_level)} confidentiality-badge">${file.confidential_level}</span>
                        <br>
                        <small>${fileSize}</small>
                        <br>
                        <small>${file.created_at}</small>
                    </div>
                    <div class="tag-cloud">${tags}</div>
                    <div class="file-actions">
                        <button class="btn btn-sm btn-outline-info" onclick="viewFileDetails(${file.id})">
                            <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="downloadFile(${file.id})">
                            <i class="bx bx-download"></i>
                        </button>
                    </div>
                </div>
            `;
            grid.append(fileHtml);
        });
    }


    // Update Breadcrumb
    function updateBreadcrumb(folder) {
        const breadcrumb = $('#breadcrumbNav .breadcrumb');
        breadcrumb.empty();
        
        breadcrumb.append('<li class="breadcrumb-item"><a href="#" onclick="loadFolderContents(0)" class="text-decoration-none"><i class="bx bx-home"></i> Root</a></li>');
        
        // Build breadcrumb path
        const path = buildBreadcrumbPath(folder.id);
        path.forEach(folder => {
            breadcrumb.append(`<li class="breadcrumb-item"><a href="#" onclick="loadFolderContents(${folder.id})" class="text-decoration-none">${folder.name}</a></li>`);
        });
    }

    // Build Breadcrumb Path
    function buildBreadcrumbPath(folderId) {
        const path = [];
        let currentId = folderId;
        
        while (currentId && currentId !== 0) {
            const folder = folderTreeData.find(f => f.id === currentId);
            if (folder) {
                path.unshift(folder);
                currentId = folder.parent_id;
            } else {
                break;
            }
        }
        
        return path;
    }

    // Handle Create Folder
    function handleCreateFolder(e) {
        e.preventDefault();
        
        const formData = $('#createFolderForm').serialize();
        const parentId = $('#folderParentId').val();
        
        // Ensure parent_id is included
        console.log('Creating folder with parent_id:', parentId);
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#createFolderModal').modal('hide');
                    $('#createFolderForm')[0].reset();
                    // Reset modal title
                    $('#createFolderModal .modal-title').html('<i class="bx bx-folder-plus"></i> Create New Folder');
                    loadFolderTree();
                    // Reload the folder where the new folder was created
                    if (parentId && parentId !== '0') {
                        loadFolderContents(parentId);
                    } else {
                        loadFolderContents(0);
                    }
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to create folder', 'error');
            }
        });
    }

    // Handle Bulk Create Folders
    function handleBulkCreateFolders(e) {
        e.preventDefault();
        
        const formData = new FormData($('#bulkCreateFoldersForm')[0]);
        
        // Ensure parent_id is explicitly set (in case form reset cleared it)
        const parentId = $('#bulkCreateFoldersParentId').val();
        formData.set('parent_id', parentId);
        
        // Log for debugging (can be removed in production)
        console.log('Bulk creating folders with parent_id:', parentId);
        
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we process your Excel file',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                if (response.success) {
                    let message = response.message;
                    if (response.errors && response.errors.length > 0) {
                        message += '\n\nErrors:\n' + response.errors.slice(0, 10).join('\n');
                        if (response.errors.length > 10) {
                            message += '\n... and ' + (response.errors.length - 10) + ' more errors';
                        }
                    }
                    
                    Swal.fire({
                        title: 'Success!',
                        html: message.replace(/\n/g, '<br>'),
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    
                    $('#bulkCreateFoldersModal').modal('hide');
                    // Reset form but preserve parent_id display
                    const currentParentId = $('#bulkCreateFoldersParentId').val();
                    const currentParentName = $('#bulkCreateFoldersParentName').html();
                    $('#bulkCreateFoldersForm')[0].reset();
                    $('#bulkCreateFoldersParentId').val(currentParentId);
                    $('#bulkCreateFoldersParentName').html(currentParentName);
                    $('#bulkCreateFoldersParentWarning').hide();
                    loadFolderTree();
                    // Reload the folder where folders were created
                    if (currentParentId && currentParentId !== '0') {
                        loadFolderContents(currentParentId);
                    } else {
                        loadFolderContents(0);
                    }
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to process Excel file', 'error');
            }
        });
    }

    // Handle Update Folder
    function handleUpdateFolder(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: $('#editFolderForm').serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#editFolderModal').modal('hide');
                    $('#editFolderForm')[0].reset();
                    loadFolderTree();
                    loadFolderContents(currentFolderId);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to update folder', 'error');
            }
        });
    }

    // Edit Folder
    window.editFolder = function(folderId, name, description, folderCode, accessLevel, departmentId) {
        $('#editFolderId').val(folderId);
        $('#editFolderName').val(name);
        $('#editFolderDescription').val(description || '');
        $('#editFolderCode').val(folderCode || '');
        $('#editAccessLevel').val(accessLevel);
        $('#editDepartmentId').val(departmentId || '');
        
        if (accessLevel === 'department') {
            $('#editDepartmentField').show();
        } else {
            $('#editDepartmentField').hide();
        }
        
        $('#editFolderModal').modal('show');
    };

    // Delete Folder
    window.deleteFolder = function(folderId, folderName) {
        Swal.fire({
            title: 'Delete Folder',
            html: `Are you sure you want to delete <strong>${folderName}</strong>?<br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'Force delete (includes subfolders and files)'
        }).then((result) => {
            if (result.isConfirmed) {
                const forceDelete = result.value === 1;
                
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'delete_folder',
                        folder_id: folderId,
                        force_delete: forceDelete
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            loadFolderTree();
                            loadFolderContents(currentFolderId);
                        } else {
                            if (response.has_subfolders || response.has_files) {
                                Swal.fire({
                                    title: 'Cannot Delete',
                                    html: response.message + '<br><br>Folder contains:<br>' +
                                          (response.has_subfolders ? '- Subfolders<br>' : '') +
                                          (response.has_files ? '- Files' : ''),
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error!', response?.message || 'Failed to delete folder', 'error');
                    }
                });
            }
        });
    };

    // Move Folder
    // Escape HTML function
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // Assign Folder to Staff
    window.assignFolderToStaff = function(folderId, folderName) {
        $('#assignFolderStaffId').val(folderId);
        $('#assignFolderStaffName').text(folderName);
        
        // Load users
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_users_for_assignment'
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#assignStaffSelect');
                    select.empty();
                    select.append('<option value="">Select Staff Member</option>');
                    response.users.forEach(user => {
                        select.append(`<option value="${user.id}">${user.name}</option>`);
                    });
                }
            }
        });
        
        $('#assignFolderToStaffModal').modal('show');
    };

    // Assign Folder to Department
    window.assignFolderToDepartment = function(folderId, folderName) {
        $('#assignFolderDeptId').val(folderId);
        $('#assignFolderDeptName').text(folderName);
        
        // Load departments
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_departments'
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#assignDepartmentSelect');
                    select.empty();
                    select.append('<option value="">Select Department</option>');
                    response.departments.forEach(dept => {
                        select.append(`<option value="${dept.id}">${dept.name}</option>`);
                    });
                }
            }
        });
        
        $('#assignFolderToDepartmentModal').modal('show');
    };

    // Handle Assign Folder to Staff Form Submission
    function handleAssignFolderToStaff(e) {
        e.preventDefault();
        
        const formData = {
            _token: csrfToken,
            action: 'assign_folder_to_staff',
            folder_id: $('#assignFolderStaffId').val(),
            user_id: $('#assignStaffSelect').val()
        };
        
        if (!formData.user_id) {
            Swal.fire('Error!', 'Please select a staff member', 'error');
            return;
        }
        
        // Show loading
        Swal.fire({
            title: 'Assigning...',
            text: 'Please wait while we assign the folder',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#assignFolderToStaffModal').modal('hide');
                        // Reload folder contents
                        if (currentFolderId) {
                            loadFolderContents(currentFolderId);
                        } else {
                            loadFolderTree();
                        }
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to assign folder', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to assign folder', 'error');
            }
        });
    }
    
    // Handle Assign Folder to Department Form Submission
    function handleAssignFolderToDepartment(e) {
        e.preventDefault();
        
        const formData = {
            _token: csrfToken,
            action: 'assign_folder_to_department',
            folder_id: $('#assignFolderDeptId').val(),
            department_id: $('#assignDepartmentSelect').val()
        };
        
        if (!formData.department_id) {
            Swal.fire('Error!', 'Please select a department', 'error');
            return;
        }
        
        // Show loading
        Swal.fire({
            title: 'Assigning...',
            text: 'Please wait while we assign the folder',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#assignFolderToDepartmentModal').modal('hide');
                        // Reload folder contents
                        if (currentFolderId) {
                            loadFolderContents(currentFolderId);
                        } else {
                            loadFolderTree();
                        }
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to assign folder', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to assign folder', 'error');
            }
        });
    }

    // View Folder Details
    window.viewFolderDetails = function(folderId) {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_folder_details',
                folder_id: folderId
            },
            success: function(response) {
                if (response.success) {
                    const folder = response.folder;
                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Folder Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Name:</strong></td><td>${escapeHtml(folder.name)}</td></tr>
                                    <tr><td><strong>Code:</strong></td><td>${escapeHtml(folder.folder_code || 'N/A')}</td></tr>
                                    <tr><td><strong>Access Level:</strong></td><td><span class="badge bg-primary">${folder.access_level}</span></td></tr>
                                    <tr><td><strong>Department:</strong></td><td>${folder.department ? escapeHtml(folder.department.name) : 'N/A'}</td></tr>
                                    <tr><td><strong>Files Count:</strong></td><td>${folder.files_count || 0}</td></tr>
                                    <tr><td><strong>Created:</strong></td><td>${folder.created_at}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Description</h6>
                                <p>${escapeHtml(folder.description || 'No description provided')}</p>
                            </div>
                        </div>
                    `;
                    Swal.fire({
                        title: 'Folder Details',
                        html: html,
                        width: '700px',
                        confirmButtonText: 'Close'
                    });
                }
            }
        });
    };

    window.moveFolder = function(folderId, folderName) {
        // Load folder tree for parent selection
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_folder_tree'
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="0">Root (Top Level)</option>';
                    
                    function buildOptions(tree, level = 0, excludeId = null) {
                        tree.forEach(folder => {
                            if (folder.id !== excludeId && folder.id !== folderId) {
                                const indent = '&nbsp;'.repeat(level * 4);
                                options += `<option value="${folder.id}">${indent}${folder.name}</option>`;
                                if (folder.subfolders && folder.subfolders.length > 0) {
                                    buildOptions(folder.subfolders, level + 1, excludeId);
                                }
                            }
                        });
                    }
                    
                    buildOptions(response.tree, 0, folderId);
                    
                    Swal.fire({
                        title: 'Move Folder',
                        html: `
                            <p>Select new parent folder for <strong>${folderName}</strong>:</p>
                            <select id="moveFolderParentSelect" class="form-select">
                                ${options}
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Move',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            $('#moveFolderParentSelect').select2({
                                dropdownParent: Swal.getContainer()
                            });
                        },
                        preConfirm: () => {
                            return $('#moveFolderParentSelect').val();
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const newParentId = result.value;
                            
                            $.ajax({
                                url: '{{ route("modules.files.digital.ajax") }}',
                                method: 'POST',
                                data: {
                                    _token: csrfToken,
                                    action: 'move_folder',
                                    folder_id: folderId,
                                    new_parent_id: newParentId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Moved!', response.message, 'success');
                                        loadFolderTree();
                                        loadFolderContents(currentFolderId);
                                    } else {
                                        Swal.fire('Error!', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    const response = xhr.responseJSON;
                                    Swal.fire('Error!', response?.message || 'Failed to move folder', 'error');
                                }
                            });
                        }
                    });
                }
            }
        });
    };

    // Create Folder Within Folder
    window.createFolderInFolder = function(folderId, folderName) {
        $('#folderParentId').val(folderId);
        // Update modal title to show parent folder
        $('#createFolderModal .modal-title').html(`<i class="bx bx-folder-plus"></i> Create Folder in "${escapeHtml(folderName)}"`);
        $('#createFolderModal').modal('show');
    };

    // Bulk Create Folders Within Folder
    window.bulkCreateFoldersInFolder = function(folderId, folderName) {
        $('#bulkCreateFoldersParentId').val(folderId);
        $('#bulkCreateFoldersParentName').html(`<i class="bx bx-folder"></i> <strong>${escapeHtml(folderName)}</strong>`);
        $('#bulkCreateFoldersParentWarning').show();
        $('#bulkCreateFoldersModal').modal('show');
    };

    // Handle Upload File
    function handleUploadFile(e) {
        e.preventDefault();
        
        const formData = new FormData($('#uploadFileForm')[0]);
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#uploadFileModal').modal('hide');
                    loadFolderContents(currentFolderId);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }

    // Handle Bulk Upload - This is now handled by Dropzone's Start Upload button
    // Keeping for backward compatibility but not used
    function handleBulkUpload(e) {
        e.preventDefault();
        // This function is no longer used as Dropzone handles the upload
        // The Start Upload button triggers Dropzone's processQueue
    }

    // Handle Advanced Search
    function handleAdvancedSearch(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: $('#advancedSearchForm').serialize(),
            success: function(response) {
                if (response.success) {
                    $('#advancedSearchModal').modal('hide');
                    displaySearchResults(response.results);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }

    // Character counter for request access purpose field
    $(document).on('input', '#requestAccessPurpose', function() {
        const length = $(this).val().length;
        const minRequired = 10;
        const charCountSpan = $('#purpose_access_char_count');
        
        charCountSpan.text(length);
        
        if (length < minRequired) {
            const moreNeeded = minRequired - length;
            charCountSpan.removeClass('text-success').addClass('text-danger fw-bold');
            charCountSpan.parent().html(
                '<span id="purpose_access_char_count" class="text-danger fw-bold">' + length + '</span>/500 characters ' +
                '<span class="text-danger">(' + moreNeeded + ' more required)</span>'
            );
        } else {
            charCountSpan.removeClass('text-danger fw-bold').addClass('text-success');
            charCountSpan.parent().html(
                '<span id="purpose_access_char_count" class="text-success">' + length + '</span>/500 characters ' +
                '<span class="text-muted">(minimum 10 characters required)</span>'
            );
        }
    });

    // Handle Request Access
    function handleRequestAccess(e) {
        e.preventDefault();
        
        const form = $('#requestAccessForm');
        const purpose = form.find('textarea[name="purpose"]').val().trim();
        const urgency = form.find('select[name="urgency"]').val();
        
        // Client-side validation
        if (!purpose || purpose.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: 'Please provide a purpose with at least <strong>10 characters</strong>.<br><br>' +
                      'Current length: <strong>' + purpose.length + ' characters</strong><br>' +
                      'Required: <strong>10 characters minimum</strong>'
            });
            form.find('textarea[name="purpose"]').focus();
            return false;
        }
        
        if (!urgency) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select an urgency level.'
            });
            form.find('select[name="urgency"]').focus();
            return false;
        }
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#requestAccessModal').modal('hide');
                    form[0].reset();
                    $('#purpose_access_char_counter').html('<span id="purpose_access_char_count">0</span>/500 characters (minimum 10 characters required)');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.status === 422) {
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

    // Display Search Results
    function displaySearchResults(results) {
        const container = $('#fileContent');
        container.html('<div class="alert alert-info"><h5><i class="bx bx-search"></i> Search Results</h5><p>Found ' + results.length + ' files</p></div>');
        
        if (results.length > 0) {
            const resultsHtml = '<div class="file-grid"></div>';
            container.append(resultsHtml);
            
            const grid = container.find('.file-grid');
            results.forEach(file => {
                const fileIcon = getFileIcon(file.mime_type);
                const fileSize = formatFileSize(file.file_size);
                
                const fileHtml = `
                    <div class="file-item" data-file-id="${file.id}">
                        <div class="file-icon text-info">
                            <i class="${fileIcon}"></i>
                        </div>
                        <div class="file-name">${file.original_name}</div>
                        <div class="file-meta">
                            <span class="badge bg-${getAccessLevelColor(file.access_level)}">${file.access_level}</span>
                            <br>
                            <small>${fileSize}</small>
                        </div>
                        <div class="file-actions">
                            <button class="btn btn-sm btn-outline-info" onclick="viewFileDetails(${file.id})">
                                <i class="bx bx-show"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="downloadFile(${file.id})">
                                <i class="bx bx-download"></i>
                            </button>
                        </div>
                    </div>
                `;
                grid.append(fileHtml);
            });
        }
    }

    // Load Analytics
    function loadAnalytics() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_analytics'
            },
            success: function(response) {
                if (response.success) {
                    renderAnalyticsCharts(response.analytics);
                }
            }
        });
    }

    // Render Analytics Charts
    function renderAnalyticsCharts(analytics) {
        // File Type Chart
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const fileTypeCtx = document.getElementById('fileTypeChart');
        if (!fileTypeCtx) return;
        
        if (fileTypeChart) fileTypeChart.destroy();
        
        fileTypeChart = new Chart(fileTypeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: analytics.file_types.map(item => item.type),
                datasets: [{
                    data: analytics.file_types.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'File Types Distribution'
                    }
                }
            }
        });

        // Storage Chart
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const storageCtx = document.getElementById('storageChart');
        if (!storageCtx) return;
        
        if (storageChart) storageChart.destroy();
        
        storageChart = new Chart(storageCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: analytics.storage_by_department.map(item => item.department),
                datasets: [{
                    label: 'Storage Used (MB)',
                    data: analytics.storage_by_department.map(item => item.storage),
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Storage by Department'
                    }
                }
            }
        });

        // Activity Chart
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const activityCtx = document.getElementById('activityChart');
        if (!activityCtx) return;
        
        if (activityChart) activityChart.destroy();
        
        activityChart = new Chart(activityCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: analytics.activity_timeline.map(item => item.date),
                datasets: [{
                    label: 'File Uploads',
                    data: analytics.activity_timeline.map(item => item.uploads),
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)'
                }, {
                    label: 'Downloads',
                    data: analytics.activity_timeline.map(item => item.downloads),
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Activity Timeline'
                    }
                }
            }
        });

        // Department Chart
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const departmentCtx = document.getElementById('departmentChart');
        if (!departmentCtx) return;
        
        if (departmentChart) departmentChart.destroy();
        
        departmentChart = new Chart(departmentCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: analytics.department_files.map(item => item.department),
                datasets: [{
                    data: analytics.department_files.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Files by Department'
                    }
                }
            }
        });
    }

    // Load My Requests
    function loadMyRequests() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_my_requests'
            },
            success: function(response) {
                if (response.success) {
                    renderMyRequests(response.requests);
                }
            }
        });
    }

    // Render My Requests
    function renderMyRequests(requests) {
        const tbody = $('#myRequestsTable tbody');
        tbody.empty();
        
        requests.forEach(request => {
            const statusBadge = getStatusBadge(request.status);
            const row = `
                <tr>
                    <td>${request.file.original_name}</td>
                    <td>${request.reason}</td>
                    <td>${statusBadge}</td>
                    <td>${request.requested_at ?? request.created_at}</td>
                    <td>
                        ${request.status === 'pending' ? 
                            `<button class="btn btn-sm btn-outline-danger" onclick="cancelRequest(${request.id})">
                                <i class="bx bx-x"></i> Cancel
                            </button>` : ''
                        }
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Load Pending Requests
    function loadPendingRequests() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_pending_requests'
            },
            success: function(response) {
                if (response.success) {
                    renderPendingRequests(response.requests);
                }
            }
        });
    }

    // Render Pending Requests
    function renderPendingRequests(requests) {
        const tbody = $('#pendingRequestsTable tbody');
        tbody.empty();
        
        requests.forEach(request => {
            const urgencyBadge = getUrgencyBadge(request.urgency);
            const row = `
                <tr>
                    <td>${(request.requester && request.requester.name) ? request.requester.name : (request.user ? request.user.name : 'Unknown')}</td>
                    <td>${request.file.original_name}</td>
                    <td>${request.reason}</td>
                    <td>${urgencyBadge}</td>
                    <td>${request.requested_at ?? request.created_at}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-success" onclick="approveRequest(${request.id})">
                            <i class="bx bx-check"></i> Approve
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="rejectRequest(${request.id})">
                            <i class="bx bx-x"></i> Reject
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Load Recent Activity
    function loadRecentActivity() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_recent_activity'
            },
            success: function(response) {
                if (response.success) {
                    renderRecentActivity(response.activities);
                }
            }
        });
    }

    // Render Recent Activity
    function renderRecentActivity(activities) {
        const container = $('#recentActivity');
        container.empty();
        
        activities.forEach(activity => {
            const activityHtml = `
                <div class="d-flex align-items-center mb-2">
                    <i class="bx ${getActivityIcon(activity.activity_type)} text-primary me-2"></i>
                    <div class="flex-grow-1">
                        <small class="text-muted">${activity.description}</small>
                        <br>
                        <small class="text-muted">${activity.created_at}</small>
                    </div>
                </div>
            `;
            container.append(activityHtml);
        });
    }

    // Update Dashboard Stats
    function updateDashboardStats() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_dashboard_stats'
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.stats);
                }
            }
        });
    }

    // Update Stats Display
    function updateStatsDisplay(stats) {
        $('.stat-card h3').eq(0).text(stats.total_files || 0);
        $('.stat-card h3').eq(1).text(stats.total_folders || 0);
        $('.stat-card h3').eq(2).text(stats.storage_used || '0 MB');
        $('.stat-card h3').eq(3).text(stats.pending_requests || 0);
    }

    // Initialize Dropzone - Modified to prevent auto-upload
    let bulkDropzone = null;
    let dropzoneInitialized = false;
    
    function initializeDropzone() {
        // Prevent multiple initializations
        if (dropzoneInitialized && bulkDropzone) {
            return bulkDropzone;
        }
        
        // Destroy existing dropzone if it exists
        if (bulkDropzone) {
            try {
                bulkDropzone.destroy();
            } catch(e) {
                console.log('Dropzone destroy error (safe to ignore):', e);
            }
        }
        
        // Remove any existing dropzone classes
        const dropzoneElement = document.getElementById('bulkDropzone');
        if (dropzoneElement) {
            dropzoneElement.classList.remove('dz-started', 'dz-clickable');
            // Remove any existing dropzone instance
            if (dropzoneElement.dropzone) {
                try {
                    dropzoneElement.dropzone.destroy();
                } catch(e) {
                    console.log('Existing dropzone destroy error:', e);
                }
            }
        }
        
        Dropzone.autoDiscover = false;
        
        const uploadUrl = '{{ route("modules.files.digital.ajax") }}';
        if (!uploadUrl) {
            console.error('Upload URL not found');
            return null;
        }
        
        bulkDropzone = new Dropzone("#bulkDropzone", {
            url: uploadUrl,
            paramName: "files",
            maxFilesize: 20, // 20MB max per file
            acceptedFiles: ".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.odt,.ods,.odp,.csv,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.tiff,.tif,.ico,.heic,.heif",
            addRemoveLinks: true,
            autoProcessQueue: false, // Don't auto-upload
            parallelUploads: 3, // Upload 3 files simultaneously for better progress visibility
            uploadMultiple: false, // Process files individually for better progress tracking
            dictDefaultMessage: "Drop documents and images here or click to select files<br><small class='text-muted'>Accepted: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, RTF, CSV, JPG, JPEG, PNG, GIF, BMP, WEBP, SVG, TIFF and other document/image formats</small>",
            dictInvalidFileType: "This file type is not allowed. Only documents and images are accepted.",
            dictFileTooBig: "File is too big. Maximum file size is 20MB.",
            dictRemoveFile: "Remove",
            accept: function(file, done) {
                // Validate file size (20MB max)
                const maxSize = 20 * 1024 * 1024; // 20MB in bytes
                if (file.size > maxSize) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    done('File size (' + fileSizeMB + 'MB) exceeds maximum allowed size of 20MB.');
                    return;
                }
                
                // Additional client-side validation for file types
                const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 
                    'odt', 'ods', 'odp', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'tiff', 'tif', 'ico', 'heic', 'heif'];
                const fileName = file.name.toLowerCase();
                const extension = fileName.split('.').pop();
                
                if (!allowedExtensions.includes(extension)) {
                    done('Only documents and images are allowed. Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, RTF, CSV, JPG, PNG, GIF, BMP, WEBP, SVG, etc.');
                    return;
                }
                
                // Check MIME type
                const allowedMimeTypes = [
                    'application/pdf', 'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain', 'text/rtf', 'application/rtf', 'text/csv', 'application/csv',
                    'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet',
                    'application/vnd.oasis.opendocument.presentation',
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
                    'image/svg+xml', 'image/tiff', 'image/x-icon', 'image/vnd.microsoft.icon',
                    'image/heic', 'image/heif'
                ];
                
                if (file.type && !allowedMimeTypes.includes(file.type)) {
                    // If extension is allowed but MIME type is not, still allow it (browser might detect wrong MIME)
                    if (!allowedExtensions.includes(extension)) {
                        done('File type not allowed. Only documents and images are accepted.');
                        return;
                    }
                }
                
                done();
            },
            init: function() {
                const dzInstance = this;
                
                // Track upload progress - declared at top level to be accessible in all handlers
                let uploadedCount = 0;
                let errorCount = 0;
                let totalFiles = 0;
                let fileProgressMap = {}; // Track individual file progress
                let overallProgressModal = null;
                let uploadStarted = false;
                let queueProcessing = false;
                let uploadTimeout = null;
                
                // Handle "Start Upload" button click
                $('#startBulkUpload').off('click').on('click', function(e) {
                    e.preventDefault();
                    
                    // Validate form
                    const folderId = $('#bulkUploadForm select[name="folder_id"]').val();
                    if (!folderId) {
                        Swal.fire('Error!', 'Please select a folder', 'error');
                        return;
                    }
                    
                    if (dzInstance.files.length === 0) {
                        Swal.fire('Error!', 'Please add at least one file to upload', 'error');
                        return;
                    }
                    
                    // Validate access level requirements
                    const accessLevel = $('#bulkAccessLevel').val();
                    if (accessLevel === 'department') {
                        const departmentId = $('#bulkDepartmentId').val();
                        if (!departmentId) {
                            Swal.fire('Error!', 'Please select a department when access level is Department', 'error');
                            return;
                        }
                    } else if (accessLevel === 'private') {
                        const assignedUsers = $('#bulkAssignedUsers').val();
                        if (!assignedUsers || assignedUsers.length === 0) {
                            Swal.fire('Error!', 'Please select at least one user when access level is Private', 'error');
                            return;
                        }
                    }
                    
                    // Disable button and show loading
                    $(this).prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Uploading...');
                    
                    // Reset counters and progress map
                    uploadedCount = 0;
                    errorCount = 0;
                    fileProgressMap = {};
                    uploadStarted = true;
                    queueProcessing = true;
                    totalFiles = dzInstance.files.length;
                    
                    // Clear any existing timeout
                    if (uploadTimeout) {
                        clearTimeout(uploadTimeout);
                        uploadTimeout = null;
                    }
                    
                    // Initialize progress for all files
                    dzInstance.files.forEach(function(file) {
                        fileProgressMap[file.name] = 0;
                    });
                    
                    // Show overall progress modal
                    showOverallProgressModal(totalFiles);
                    
                    // Set a timeout to check if queue is stuck (after 5 minutes)
                    uploadTimeout = setTimeout(function() {
                        if (queueProcessing) {
                            const processed = uploadedCount + errorCount;
                            if (processed < totalFiles) {
                                console.warn('Upload queue appears stuck. Processed:', processed, 'Total:', totalFiles);
                                // Force continue queue if it's stuck
                                if (dzInstance.getQueuedFiles().length > 0) {
                                    console.log('Forcing queue to continue...');
                                    dzInstance.processQueue();
                                }
                            }
                        }
                    }, 300000); // 5 minutes
                    
                    // Process all files
                    dzInstance.processQueue();
                });
                
                // Update form data when sending each file
                this.on("sending", function(file, xhr, formData) {
                    formData.append("_token", csrfToken);
                    formData.append("action", "bulk_upload");
                    formData.append("folder_id", $('#bulkUploadForm select[name="folder_id"]').val());
                    formData.append("access_level", $('#bulkUploadForm select[name="access_level"]').val());
                    formData.append("confidential_level", $('#bulkUploadForm select[name="confidential_level"]').val());
                    formData.append("tags", $('#bulkUploadForm input[name="tags"]').val());
                    
                    // Add department_id if access level is department
                    const accessLevel = $('#bulkUploadForm select[name="access_level"]').val();
                    if (accessLevel === 'department') {
                        const departmentId = $('#bulkDepartmentId').val();
                        if (departmentId) {
                            formData.append("department_id", departmentId);
                        }
                    }
                    
                    // Add assigned_users if access level is private
                    if (accessLevel === 'private') {
                        const assignedUsers = $('#bulkAssignedUsers').val();
                        if (assignedUsers && assignedUsers.length > 0) {
                            assignedUsers.forEach(userId => {
                                formData.append("assigned_users[]", userId);
                            });
                        }
                    }
                });
                
                // Update total files count when files are added
                this.on("addedfile", function(file) {
                    totalFiles = this.files.length;
                    updateBulkUploadFileCount(totalFiles);
                    // Initialize progress for this file
                    fileProgressMap[file.name] = 0;
                    // Reset counters only if upload hasn't started
                    if (!uploadStarted) {
                        uploadedCount = 0;
                        errorCount = 0;
                    }
                    
                    // Ensure progress bar is visible for this file
                    setTimeout(function() {
                        const progressBar = file.previewElement.querySelector('.dz-progress');
                        if (progressBar) {
                            progressBar.style.display = 'block';
                            const progressBarInner = progressBar.querySelector('.dz-upload');
                            if (progressBarInner) {
                                progressBarInner.style.width = '0%';
                            }
                        }
                    }, 100);
                });
                
                // Update total files count when files are removed
                this.on("removedfile", function(file) {
                    totalFiles = this.files.length;
                    updateBulkUploadFileCount(totalFiles);
                    delete fileProgressMap[file.name];
                });
                
                // Reset counters when starting upload
                this.on("sending", function(file) {
                    // Mark upload as started
                    uploadStarted = true;
                    // Initialize progress for this file
                    fileProgressMap[file.name] = 0;
                    
                    // Show "Uploading..." status
                    let progressText = file.previewElement.querySelector('.dz-progress-text');
                    if (!progressText) {
                        progressText = document.createElement('div');
                        progressText.className = 'dz-progress-text';
                        progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #007bff; font-weight: bold;';
                        const progressBar = file.previewElement.querySelector('.dz-progress');
                        if (progressBar && progressBar.parentElement) {
                            progressBar.parentElement.appendChild(progressText);
                        }
                    }
                    progressText.textContent = 'Starting...';
                    progressText.style.color = '#007bff';
                });
                
                // Show queued status for files waiting to upload
                this.on("processing", function(file) {
                    let progressText = file.previewElement.querySelector('.dz-progress-text');
                    if (!progressText) {
                        progressText = document.createElement('div');
                        progressText.className = 'dz-progress-text';
                        progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #6c757d; font-weight: bold;';
                        const progressBar = file.previewElement.querySelector('.dz-progress');
                        if (progressBar && progressBar.parentElement) {
                            progressBar.parentElement.appendChild(progressText);
                        }
                    }
                    progressText.textContent = 'Queued...';
                    progressText.style.color = '#6c757d';
                });
                
                // Show upload progress with percentage
                this.on("uploadprogress", function(file, progress, bytesSent) {
                    // Update individual file progress
                    fileProgressMap[file.name] = progress;
                    
                    const progressBar = file.previewElement.querySelector('.dz-progress');
                    if (progressBar) {
                        const progressBarInner = progressBar.querySelector('.dz-upload');
                        if (progressBarInner) {
                            progressBarInner.style.width = progress + '%';
                        }
                        // Update progress text if it exists
                        let progressText = file.previewElement.querySelector('.dz-progress-text');
                        if (!progressText) {
                            progressText = document.createElement('div');
                            progressText.className = 'dz-progress-text';
                            progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #007bff; font-weight: bold;';
                            progressBar.parentElement.appendChild(progressText);
                        }
                        progressText.textContent = Math.round(progress) + '%';
                    }
                    
                    // Update overall progress
                    updateOverallProgress();
                });
                
                this.on("success", function(file, response) {
                    // Mark file as 100% complete
                    fileProgressMap[file.name] = 100;
                    
                    // Parse response if it's a string
                    let parsedResponse = response;
                    if (typeof response === 'string') {
                        try {
                            parsedResponse = JSON.parse(response);
                        } catch(e) {
                            console.error('Failed to parse response for file:', file.name, e);
                            parsedResponse = { success: false, message: 'Invalid response from server' };
                        }
                    }
                    
                    if (parsedResponse && parsedResponse.success) {
                        uploadedCount++;
                        console.log('File uploaded successfully:', file.name, 'Total uploaded:', uploadedCount);
                        file.previewElement.classList.add("dz-success");
                        file.previewElement.classList.remove("dz-error");
                        // Update progress text to show success - create if doesn't exist
                        let progressText = file.previewElement.querySelector('.dz-progress-text');
                        if (!progressText) {
                            progressText = document.createElement('div');
                            progressText.className = 'dz-progress-text';
                            progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #28a745; font-weight: bold;';
                            const progressBar = file.previewElement.querySelector('.dz-progress');
                            if (progressBar && progressBar.parentElement) {
                                progressBar.parentElement.appendChild(progressText);
                            } else {
                                // If no progress bar, append to preview element
                                const previewElement = file.previewElement.querySelector('.dz-preview');
                                if (previewElement) {
                                    previewElement.appendChild(progressText);
                                }
                            }
                        }
                        progressText.textContent = '100% ✓';
                        progressText.style.color = '#28a745';
                    } else {
                        errorCount++;
                        file.previewElement.classList.add("dz-error");
                        file.previewElement.classList.remove("dz-success");
                        const errorMsg = parsedResponse && parsedResponse.message ? parsedResponse.message : 'Upload failed';
                        let errorElement = file.previewElement.querySelector('.dz-error-message');
                        if (!errorElement) {
                            errorElement = document.createElement('div');
                            errorElement.className = 'dz-error-message';
                            file.previewElement.querySelector('.dz-preview').appendChild(errorElement);
                        }
                        errorElement.textContent = errorMsg;
                        errorElement.style.color = '#dc3545';
                        // Update progress text to show error - create if doesn't exist
                        let progressText = file.previewElement.querySelector('.dz-progress-text');
                        if (!progressText) {
                            progressText = document.createElement('div');
                            progressText.className = 'dz-progress-text';
                            progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #dc3545; font-weight: bold;';
                            const progressBar = file.previewElement.querySelector('.dz-progress');
                            if (progressBar && progressBar.parentElement) {
                                progressBar.parentElement.appendChild(progressText);
                            } else {
                                // If no progress bar, append to preview element
                                const previewElement = file.previewElement.querySelector('.dz-preview');
                                if (previewElement) {
                                    previewElement.appendChild(progressText);
                                }
                            }
                        }
                        progressText.textContent = 'Failed ✗';
                        progressText.style.color = '#dc3545';
                    }
                    
                    // Update overall progress
                    updateOverallProgress();
                    
                    // Check if all files are processed
                    checkUploadCompletion();
                    
                    // Continue processing queue even if there was an error
                    // This ensures all files are attempted
                    setTimeout(function() {
                        if (dzInstance.getQueuedFiles().length > 0) {
                            dzInstance.processQueue();
                        }
                    }, 100);
                });
                
                this.on("error", function(file, errorMessage, xhr) {
                    // Mark file as failed
                    fileProgressMap[file.name] = 0;
                    errorCount++;
                    console.log('File upload error:', file.name, 'Total errors:', errorCount);
                    file.previewElement.classList.add("dz-error");
                    file.previewElement.classList.remove("dz-success");
                    
                    // Try to parse error response
                    let errorMsg = 'Upload failed';
                    if (xhr && xhr.responseText) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            errorMsg = errorResponse.message || errorResponse.error || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }
                    } else if (typeof errorMessage === 'string') {
                        errorMsg = errorMessage;
                    } else if (errorMessage && errorMessage.message) {
                        errorMsg = errorMessage.message;
                    }
                    
                    let errorElement = file.previewElement.querySelector('.dz-error-message');
                    if (!errorElement) {
                        errorElement = document.createElement('div');
                        errorElement.className = 'dz-error-message';
                        file.previewElement.querySelector('.dz-preview').appendChild(errorElement);
                    }
                    errorElement.textContent = errorMsg;
                    errorElement.style.color = '#dc3545';
                    
                    // Update progress text to show error - create if doesn't exist
                    let progressText = file.previewElement.querySelector('.dz-progress-text');
                    if (!progressText) {
                        progressText = document.createElement('div');
                        progressText.className = 'dz-progress-text';
                        progressText.style.cssText = 'text-align: center; margin-top: 5px; font-size: 12px; color: #dc3545; font-weight: bold;';
                        const progressBar = file.previewElement.querySelector('.dz-progress');
                        if (progressBar && progressBar.parentElement) {
                            progressBar.parentElement.appendChild(progressText);
                        } else {
                            // If no progress bar, append to preview element
                            const previewElement = file.previewElement.querySelector('.dz-preview');
                            if (previewElement) {
                                previewElement.appendChild(progressText);
                            }
                        }
                    }
                    progressText.textContent = 'Failed ✗';
                    progressText.style.color = '#dc3545';
                    
                    // Update overall progress
                    updateOverallProgress();
                    
                    // Check if all files are processed
                    checkUploadCompletion();
                    
                    // Continue processing queue even after error
                    // This ensures all files are attempted
                    setTimeout(function() {
                        if (dzInstance.getQueuedFiles().length > 0) {
                            dzInstance.processQueue();
                        }
                    }, 100);
                });
                
                // Handle queue completion as backup
                this.on("queuecomplete", function() {
                    console.log('Queue complete event fired');
                    queueProcessing = false;
                    if (uploadTimeout) {
                        clearTimeout(uploadTimeout);
                        uploadTimeout = null;
                    }
                    // Give a small delay to ensure all success/error handlers have fired
                    setTimeout(function() {
                        checkUploadCompletion();
                    }, 500);
                });
                
                // Handle when queue is empty (all files processed)
                this.on("complete", function(file) {
                    // This fires for each file when it's complete (success or error)
                    // We use this to ensure queue continues
                    if (dzInstance.getQueuedFiles().length > 0 && queueProcessing) {
                        // Continue processing remaining files
                        setTimeout(function() {
                            dzInstance.processQueue();
                        }, 50);
                    }
                });
                
                // Show overall progress modal
                function showOverallProgressModal(total) {
                    if (overallProgressModal) {
                        Swal.close();
                    }
                    overallProgressModal = Swal.fire({
                        title: 'Uploading Files...',
                        html: `
                            <div class="text-center">
                                <div class="mb-3">
                                    <div class="progress" style="height: 30px; border-radius: 15px;">
                                        <div id="overallProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 0%; font-size: 14px; line-height: 30px; font-weight: bold;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-2">
                                    <strong id="overallProgressText">0</strong> of <strong>${total}</strong> files uploaded
                                </p>
                                <p class="text-muted small mb-0" id="overallProgressDetails">
                                    Starting upload...
                                </p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
                
                // Update overall progress
                function updateOverallProgress() {
                    if (!overallProgressModal || totalFiles === 0) return;
                    
                    // Calculate overall progress from all files
                    let totalProgress = 0;
                    let completedFiles = 0;
                    Object.keys(fileProgressMap).forEach(function(fileName) {
                        totalProgress += fileProgressMap[fileName];
                        if (fileProgressMap[fileName] >= 100) {
                            completedFiles++;
                        }
                    });
                    
                    const overallPercentage = totalFiles > 0 ? Math.round(totalProgress / totalFiles) : 0;
                    const processedCount = uploadedCount + errorCount;
                    
                    // Update progress bar
                    const progressBar = document.getElementById('overallProgressBar');
                    if (progressBar) {
                        progressBar.style.width = overallPercentage + '%';
                        progressBar.textContent = overallPercentage + '%';
                        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
                        if (overallPercentage === 100) {
                            progressBar.classList.remove('progress-bar-animated');
                            progressBar.classList.add('bg-success');
                        }
                    }
                    
                    // Update progress text
                    const progressText = document.getElementById('overallProgressText');
                    if (progressText) {
                        progressText.textContent = processedCount;
                    }
                    
                    // Update details
                    const progressDetails = document.getElementById('overallProgressDetails');
                    if (progressDetails) {
                        if (processedCount < totalFiles) {
                            progressDetails.textContent = `Uploading... ${uploadedCount} successful, ${errorCount} failed`;
                        } else {
                            progressDetails.textContent = `Upload complete! ${uploadedCount} successful, ${errorCount} failed`;
                        }
                    }
                }
                
                // Helper function to check upload completion
                function checkUploadCompletion() {
                    const processedCount = uploadedCount + errorCount;
                    const queuedFiles = dzInstance.getQueuedFiles().length;
                    const processingFiles = dzInstance.getUploadingFiles().length;
                    
                    console.log('Check completion - Processed:', processedCount, 'Total:', totalFiles, 'Queued:', queuedFiles, 'Processing:', processingFiles);
                    
                    // Check if all files are done (processed + queued + processing = 0)
                    if (processedCount >= totalFiles && totalFiles > 0 && queuedFiles === 0 && processingFiles === 0) {
                        queueProcessing = false;
                        
                        // Clear timeout
                        if (uploadTimeout) {
                            clearTimeout(uploadTimeout);
                            uploadTimeout = null;
                        }
                        
                        // Close overall progress modal
                        if (overallProgressModal) {
                            Swal.close();
                            overallProgressModal = null;
                        }
                        
                        $('#startBulkUpload').prop('disabled', false).html('Start Upload');
                        uploadStarted = false;
                        
                        if (errorCount === 0 && uploadedCount > 0) {
                            // All successful
                            Swal.fire({
                                icon: 'success',
                                title: 'Upload Complete!',
                                html: `<div class="text-center">
                                    <i class="bx bx-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                                    <h5 class="mt-3">All ${uploadedCount} file(s) uploaded successfully!</h5>
                                    <p class="text-muted">Files have been added to the selected folder.</p>
                                </div>`,
                                confirmButtonText: 'OK',
                                width: '500px'
                            }).then(() => {
                                $('#bulkUploadModal').modal('hide');
                                if (currentFolderId) {
                                    loadFolderContents(currentFolderId);
                                }
                                updateDashboardStats();
                            });
                        } else if (uploadedCount > 0 && errorCount > 0) {
                            // Partial success
                            Swal.fire({
                                icon: 'warning',
                                title: 'Partial Upload',
                                html: `<div>
                                    <p><strong>${uploadedCount}</strong> file(s) uploaded successfully</p>
                                    <p><strong>${errorCount}</strong> file(s) failed to upload</p>
                                    <p class="text-muted small">Please check the file list above for details.</p>
                                </div>`,
                                confirmButtonText: 'OK',
                                width: '500px'
                            }).then(() => {
                                if (currentFolderId) {
                                    loadFolderContents(currentFolderId);
                                }
                                updateDashboardStats();
                            });
                        } else if (errorCount === totalFiles) {
                            // All failed
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                html: `<div class="text-center">
                                    <i class="bx bx-x-circle" style="font-size: 3rem; color: #dc3545;"></i>
                                    <h5 class="mt-3">All ${errorCount} file(s) failed to upload</h5>
                                    <p class="text-muted">Please check the file list above for error details.</p>
                                </div>`,
                                confirmButtonText: 'OK',
                                width: '500px'
                            });
                        }
                        
                        // Reset counters for next upload
                        uploadedCount = 0;
                        errorCount = 0;
                        totalFiles = 0;
                        fileProgressMap = {};
                    }
                }
                
                // Reset when modal is hidden
                $('#bulkUploadModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    // Close overall progress modal if still open
                    if (overallProgressModal) {
                        Swal.close();
                        overallProgressModal = null;
                    }
                    
                    if (dzInstance && dzInstance.files.length > 0) {
                        dzInstance.removeAllFiles(true);
                    }
                    $('#startBulkUpload').prop('disabled', false).html('Start Upload');
                    // Reset form
                    $('#bulkUploadForm')[0].reset();
                    $('#bulkAccessLevel').val('public');
                    $('#bulkDepartmentField').hide();
                    $('#bulkDepartmentId').prop('required', false).val('');
                    $('#bulkAssignedUsersField').hide();
                    $('#bulkAssignedUsers').prop('required', false).val(null).trigger('change');
                    $('#bulkUploadInfo').hide();
                    updateBulkUploadFileCount(0);
                    // Reset progress tracking
                    uploadedCount = 0;
                    errorCount = 0;
                    totalFiles = 0;
                    fileProgressMap = {};
                    uploadStarted = false;
                    queueProcessing = false;
                    if (uploadTimeout) {
                        clearTimeout(uploadTimeout);
                        uploadTimeout = null;
                    }
                    // Reset initialization flag so it can be reinitialized next time
                    dropzoneInitialized = false;
                });
                
                // Mark as initialized
                dropzoneInitialized = true;
            }
        });
        
        return bulkDropzone;
    }

    // Initialize Charts
    function initializeCharts() {
        // Created on demand by loadAnalytics()
    }

    // Load Analytics and render charts
    function loadAnalytics() {
        // Show loading state
        $('#analyticsModal .modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading analytics...</p></div>');
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: { _token: csrfToken, action: 'get_analytics' },
            success: function(response) {
                if (!response.success) {
                    $('#analyticsModal .modal-body').html('<div class="alert alert-danger"><i class="bx bx-error-circle"></i> Failed to load analytics. Please try again.</div>');
                    return;
                }
                
                // Restore chart containers
                const analyticsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <h6 class="text-center mb-3"><i class="bx bx-pie-chart"></i> File Types Distribution</h6>
                                <canvas id="fileTypeChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <h6 class="text-center mb-3"><i class="bx bx-bar-chart"></i> Storage by Department</h6>
                                <canvas id="storageChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <h6 class="text-center mb-3"><i class="bx bx-line-chart"></i> Activity Timeline (Last 30 Days)</h6>
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <h6 class="text-center mb-3"><i class="bx bx-bar-chart-alt"></i> Files by Department</h6>
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                `;
                $('#analyticsModal .modal-body').html(analyticsHtml);
                
                const a = response.analytics || {};
                
                // Small delay to ensure canvas elements are rendered and Chart.js is loaded
                setTimeout(() => {
                    console.log('=== Starting File Analytics Chart Rendering ===');
                    console.log('Analytics data:', a);
                    
                    // Check if Chart.js is available
                    if (typeof Chart === 'undefined') {
                        console.warn('Chart.js not loaded, attempting to load from CDN...');
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
                        script.onload = function() {
                            console.log('Chart.js loaded from CDN, rendering all charts...');
                            renderFileTypeChart(a.file_types || []);
                            renderStorageChart(a.storage_by_department || []);
                            renderActivityChart(a.activity_timeline || []);
                            renderDepartmentFilesChart(a.department_files || []);
                        };
                        script.onerror = function() {
                            console.error('Failed to load Chart.js from CDN');
                            $('#analyticsModal .modal-body').prepend('<div class="alert alert-danger"><i class="bx bx-error-circle"></i> Chart library could not be loaded. Please refresh the page.</div>');
                        };
                        document.head.appendChild(script);
                    } else {
                        console.log('Chart.js is available, rendering charts...');
                        renderFileTypeChart(a.file_types || []);
                        renderStorageChart(a.storage_by_department || []);
                        renderActivityChart(a.activity_timeline || []);
                        renderDepartmentFilesChart(a.department_files || []);
                        console.log('=== File Analytics Charts Rendering Complete ===');
                    }
                }, 300);
            },
            error: function(xhr, status, error) {
                $('#analyticsModal .modal-body').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle"></i> 
                        <strong>Error loading analytics:</strong> ${error || 'Unknown error occurred. Please try again.'}
                    </div>
                `);
            }
        });
    }

    function destroyChartIfExists(chart) {
        if (chart && typeof chart.destroy === 'function') chart.destroy();
    }

    function renderFileTypeChart(items) {
        console.log('=== Rendering File Type Chart ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Data items:', items);
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, retrying chart render...');
                renderFileTypeChart(items);
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                const ctx = document.getElementById('fileTypeChart');
                if (ctx) {
                    ctx.parentElement.innerHTML = '<div class="alert alert-danger">Chart library could not be loaded.</div>';
                }
            };
            document.head.appendChild(script);
            return;
        }
        
        const ctx = document.getElementById('fileTypeChart');
        if (!ctx) {
            console.warn('File Type Chart canvas element not found');
            return;
        }
        
        const labels = items.length > 0 ? items.map(i => i.type || 'Unknown') : ['No Data'];
        const data = items.length > 0 ? items.map(i => Number(i.count) || 0) : [0];
        destroyChartIfExists(fileTypeChart);
        fileTypeChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: { 
                labels, 
                datasets: [{ 
                    data, 
                    backgroundColor: ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc949','#af7aa1','#ff9da7','#9c755f','#bab0ac'] 
                }] 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: true,
                plugins: { 
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        console.log('✅ File Type Chart rendered successfully');
    }

    function renderStorageChart(items) {
        console.log('=== Rendering Storage Chart ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Data items:', items);
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, retrying chart render...');
                renderStorageChart(items);
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                const ctx = document.getElementById('storageChart');
                if (ctx) {
                    ctx.parentElement.innerHTML = '<div class="alert alert-danger">Chart library could not be loaded.</div>';
                }
            };
            document.head.appendChild(script);
            return;
        }
        
        const ctx = document.getElementById('storageChart');
        if (!ctx) {
            console.warn('Storage Chart canvas element not found');
            return;
        }
        
        const labels = items.length > 0 ? items.map(i => i.department || 'Unknown') : ['No Data'];
        // Convert bytes to MB for better readability
        const data = items.length > 0 ? items.map(i => {
            const bytes = Number(i.storage) || 0;
            return parseFloat((bytes / (1024 * 1024)).toFixed(2)); // Convert to MB
        }) : [0];
        
        destroyChartIfExists(storageChart);
        storageChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: { 
                labels, 
                datasets: [{ 
                    label: 'Storage (MB)', 
                    data, 
                    backgroundColor: '#59a14f' 
                }] 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: true,
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1024) {
                                    return (value / 1024).toFixed(2) + ' GB';
                                }
                                return value.toFixed(2) + ' MB';
                            }
                        }
                    } 
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y || 0;
                                const mb = value.toFixed(2);
                                const gb = (value / 1024).toFixed(2);
                                return `${context.dataset.label}: ${mb} MB (${gb} GB)`;
                            }
                        }
                    }
                }
            }
        });
        console.log('✅ Storage Chart rendered successfully');
    }

    function renderActivityChart(items) {
        console.log('=== Rendering Activity Chart ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Data items:', items);
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, retrying chart render...');
                renderActivityChart(items);
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                const ctx = document.getElementById('activityChart');
                if (ctx) {
                    ctx.parentElement.innerHTML = '<div class="alert alert-danger">Chart library could not be loaded.</div>';
                }
            };
            document.head.appendChild(script);
            return;
        }
        
        const ctx = document.getElementById('activityChart');
        if (!ctx) {
            console.warn('Activity Chart canvas element not found');
            return;
        }
        
        const labels = items.length > 0 ? items.map(i => {
            // Format date to be more readable
            const date = new Date(i.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }) : ['No Data'];
        const uploads = items.length > 0 ? items.map(i => Number(i.uploads) || 0) : [0];
        const downloads = items.length > 0 ? items.map(i => Number(i.downloads) || 0) : [0];
        
        destroyChartIfExists(activityChart);
        activityChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: { 
                labels, 
                datasets: [
                    { 
                        label: 'Uploads', 
                        data: uploads, 
                        borderColor: '#4e79a7', 
                        backgroundColor: 'rgba(78,121,167,0.2)', 
                        tension: 0.4, 
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    { 
                        label: 'Downloads', 
                        data: downloads, 
                        borderColor: '#e15759', 
                        backgroundColor: 'rgba(225,87,89,0.2)', 
                        tension: 0.4, 
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ] 
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        console.log('✅ Activity Chart rendered successfully');
    }

    function renderDepartmentFilesChart(items) {
        console.log('=== Rendering Department Files Chart ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Data items:', items);
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, retrying chart render...');
                renderDepartmentFilesChart(items);
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                const ctx = document.getElementById('departmentChart');
                if (ctx) {
                    ctx.parentElement.innerHTML = '<div class="alert alert-danger">Chart library could not be loaded.</div>';
                }
            };
            document.head.appendChild(script);
            return;
        }
        
        const ctx = document.getElementById('departmentChart');
        if (!ctx) {
            console.warn('Department Files Chart canvas element not found');
            return;
        }
        
        const labels = items.length > 0 ? items.map(i => i.department || 'Unknown') : ['No Data'];
        const data = items.length > 0 ? items.map(i => Number(i.count) || 0) : [0];
        
        destroyChartIfExists(departmentChart);
        departmentChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: { 
                labels, 
                datasets: [{ 
                    label: 'Files', 
                    data, 
                    backgroundColor: '#f28e2b' 
                }] 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: true,
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    } 
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y || 0;
                                return `${label}: ${value} file(s)`;
                            }
                        }
                    }
                }
            }
        });
        console.log('✅ Department Files Chart rendered successfully');
    }

    // Initialize Drag and Drop
    function initializeDragAndDrop() {
        // Add drag and drop functionality for file uploads
        const uploadArea = $('.file-upload-area');
        
        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        uploadArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                // Handle file drop
                handleFileDrop(files);
            }
        });
    }

    // Handle File Drop
    function handleFileDrop(files) {
        // Implementation for handling dropped files
        console.log('Files dropped:', files);
    }

    // Populate Folder Select
    function populateFolderSelect(selector) {
        const select = $(selector);
        select.empty();
        select.append('<option value="">Loading folders...</option>');
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_all_folders'
            },
            success: function(response) {
                select.empty();
                select.append('<option value="">Select Folder</option>');
                if (response.success) {
                    response.folders.forEach(f => {
                        select.append(`<option value="${f.id}">${f.display_name}</option>`);
                    });
                }
                if (currentFolderId) {
                    select.val(String(currentFolderId));
                }
            },
            error: function() {
                select.empty();
                select.append('<option value="">Failed to load folders</option>');
            }
        });
    }

    // Load Users for Assignment
    function loadUsersForAssignment() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_users_for_assignment'
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#assignedUsersField select');
                    select.empty();
                    
                    response.users.forEach(user => {
                        select.append(`<option value="${user.id}">${user.name}</option>`);
                    });
                    
                    select.select2({
                        placeholder: 'Select users',
                        allowClear: true
                    });
                }
            }
        });
    }
    
    // Load Users for Bulk Assignment
    function loadUsersForBulkAssignment() {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_users_for_assignment'
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#bulkAssignedUsers');
                    select.empty();
                    
                    response.users.forEach(user => {
                        select.append(`<option value="${user.id}">${user.name}</option>`);
                    });
                    
                    // Initialize Select2 if available
                    if (typeof $.fn.select2 !== 'undefined') {
                        select.select2({
                            placeholder: 'Select users',
                            allowClear: true,
                            dropdownParent: $('#bulkUploadModal')
                        });
                    }
                }
            }
        });
    }
    
    // Update bulk upload file count display
    function updateBulkUploadFileCount(count) {
        const infoDiv = $('#bulkUploadInfo');
        const countSpan = $('#bulkFileCount');
        
        if (count > 0) {
            countSpan.text(count);
            infoDiv.show();
        } else {
            infoDiv.hide();
        }
    }

    // Utility Functions
    function getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) return 'bx-file-pdf';
        if (mimeType.includes('image')) return 'bx-image';
        if (mimeType.includes('video')) return 'bx-video';
        if (mimeType.includes('audio')) return 'bx-music';
        if (mimeType.includes('word') || mimeType.includes('document')) return 'bx-file-doc';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'bx-file-xls';
        if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'bx-file-ppt';
        if (mimeType.includes('zip') || mimeType.includes('archive')) return 'bx-file-zip';
        return 'bx-file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getAccessLevelColor(level) {
        const colors = {
            'public': 'success',
            'department': 'info',
            'private': 'warning'
        };
        return colors[level] || 'secondary';
    }

    function getConfidentialityColor(level) {
        const colors = {
            'normal': 'success',
            'confidential': 'warning',
            'strictly_confidential': 'danger'
        };
        return colors[level] || 'secondary';
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pending</span>',
            'approved': '<span class="badge bg-success">Approved</span>',
            'rejected': '<span class="badge bg-danger">Rejected</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
    }

    function getUrgencyBadge(urgency) {
        const badges = {
            'low': '<span class="badge bg-success">Low</span>',
            'medium': '<span class="badge bg-warning">Medium</span>',
            'high': '<span class="badge bg-danger">High</span>',
            'urgent': '<span class="badge bg-danger">Urgent</span>'
        };
        return badges[urgency] || '<span class="badge bg-secondary">Unknown</span>';
    }

    function getActivityIcon(type) {
        const icons = {
            'file_upload': 'bx-upload',
            'file_download': 'bx-download',
            'folder_created': 'bx-folder-plus',
            'access_request': 'bx-key',
            'access_granted': 'bx-check-circle'
        };
        return icons[type] || 'bx-info-circle';
    }

    // Global Functions
    window.loadFolderContents = loadFolderContents;
    window.viewFileDetails = function(fileId) {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'view_file_details',
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    displayFileDetails(response.file);
                    $('#fileDetailsModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load file details', 'error');
            }
        });
    };
    
    window.downloadFile = function(fileId) {
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'download_file',
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    // Create a temporary link to download the file
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = response.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    Swal.fire('Success!', 'File download started', 'success');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to download file', 'error');
            }
        });
    };
    window.approveRequest = function(requestId) {
        Swal.fire({
            title: 'Approve Request',
            text: 'Are you sure you want to approve this access request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'approve_request',
                        request_id: requestId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Approved!', response.message, 'success');
                            loadPendingRequests(); // Refresh the list
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to approve request', 'error');
                    }
                });
            }
        });
    };
    
    window.rejectRequest = function(requestId) {
        Swal.fire({
            title: 'Reject Request',
            text: 'Please provide a reason for rejection:',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Reject Request',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for rejection';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'reject_request',
                        request_id: requestId,
                        reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rejected!', response.message, 'success');
                            loadPendingRequests(); // Refresh the list
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to reject request', 'error');
                    }
                });
            }
        });
    };
    window.cancelRequest = function(requestId) {
        Swal.fire({
            title: 'Cancel Request',
            text: 'Are you sure you want to cancel this access request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'cancel_request',
                        request_id: requestId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Cancelled!', response.message, 'success');
                            loadMyRequests(); // Refresh the list
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to cancel request', 'error');
                    }
                });
            }
        });
    };
    
    // Display File Details
    function displayFileDetails(file) {
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bx bx-file"></i> File Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Name:</strong></td><td>${file.original_name}</td></tr>
                        <tr><td><strong>Size:</strong></td><td>${file.file_size}</td></tr>
                        <tr><td><strong>Type:</strong></td><td>${file.mime_type}</td></tr>
                        <tr><td><strong>Uploaded:</strong></td><td>${file.created_at}</td></tr>
                        <tr><td><strong>Downloads:</strong></td><td>${file.download_count}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="bx bx-shield"></i> Access Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Access Level:</strong></td><td><span class="badge bg-${getAccessLevelColor(file.access_level)}">${file.access_level}</span></td></tr>
                        <tr><td><strong>Confidentiality:</strong></td><td><span class="badge bg-${getConfidentialityColor(file.confidential_level)}">${file.confidential_level}</span></td></tr>
                        <tr><td><strong>Uploaded By:</strong></td><td>${file.uploaded_by}</td></tr>
                        <tr><td><strong>Folder:</strong></td><td>${file.folder}</td></tr>
                    </table>
                </div>
            </div>
            ${file.description ? `<div class="mt-3"><h6>Description:</h6><p>${file.description}</p></div>` : ''}
            ${file.tags ? `<div class="mt-3"><h6>Tags:</h6><div class="tag-cloud">${file.tags.split(',').map(tag => `<span class="tag">${tag.trim()}</span>`).join('')}</div></div>` : ''}
            ${file.assigned_users && file.assigned_users.length > 0 ? `
                <div class="mt-3">
                    <h6><i class="bx bx-user"></i> Assigned Users</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Assigned</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${file.assigned_users.map(user => `
                                    <tr>
                                        <td>${user.name}</td>
                                        <td>${user.email}</td>
                                        <td>${user.assigned_at}</td>
                                        <td>${user.expiry_date}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` : ''}
            ${file.recent_activities && file.recent_activities.length > 0 ? `
                <div class="mt-3">
                    <h6><i class="bx bx-history"></i> Recent Activity</h6>
                    <div class="activity-timeline">
                        ${file.recent_activities.map(activity => `
                            <div class="activity-item">
                                <div class="d-flex align-items-center">
                                    <i class="bx ${getActivityIcon(activity.type)} text-primary me-2"></i>
                                    <div class="flex-grow-1">
                                        <small class="text-muted">${activity.user} - ${activity.date}</small>
                                        <br>
                                        <small>${activity.details || activity.type}</small>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        `;
        
        $('#fileDetailsContent').html(content);
        $('#downloadFileBtn').off('click').on('click', function() {
            downloadFile(file.id);
        });
        $('#requestAccessBtn').off('click').on('click', function() {
            $('#requestFileId').val(file.id);
            $('#requestAccessModal').modal('show');
        });
    }

    // Bulk Operations Functions
    function updateBulkActionsToolbar() {
        const checked = $('.folder-checkbox:checked');
        const count = checked.length;
        const toolbar = $('#bulkActionsToolbar');
        
        if (count > 0) {
            toolbar.show();
            $('#selectedCount').text(count + ' selected');
        } else {
            toolbar.hide();
        }
    }

    function getSelectedFolderIds() {
        return $('.folder-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function selectAllFolders() {
        $('.folder-checkbox').prop('checked', true);
        updateBulkActionsToolbar();
    }

    function deselectAllFolders() {
        $('.folder-checkbox').prop('checked', false);
        updateBulkActionsToolbar();
    }

    // Bulk Delete Folders
    window.bulkDeleteFolders = function() {
        const folderIds = getSelectedFolderIds();
        
        if (folderIds.length === 0) {
            Swal.fire('Warning!', 'Please select at least one folder to delete', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Delete Folders',
            html: `Are you sure you want to delete <strong>${folderIds.length}</strong> folder(s)?<br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'Force delete (includes subfolders and files)'
        }).then((result) => {
            if (result.isConfirmed) {
                const forceDelete = result.value === 1;
                
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the folders',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.ajax({
                    url: '{{ route("modules.files.digital.ajax") }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'bulk_delete_folders',
                        folder_ids: folderIds,
                        force_delete: forceDelete
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            deselectAllFolders();
                            loadFolderTree();
                            loadFolderContents(currentFolderId);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        const response = xhr.responseJSON;
                        Swal.fire('Error!', response?.message || 'Failed to delete folders', 'error');
                    }
                });
            }
        });
    };

    // Bulk Move Folders
    window.bulkMoveFolders = function() {
        const folderIds = getSelectedFolderIds();
        
        if (folderIds.length === 0) {
            Swal.fire('Warning!', 'Please select at least one folder to move', 'warning');
            return;
        }
        
        // Load folder tree for parent selection
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_folder_tree'
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="0">Root (Top Level)</option>';
                    
                    function buildOptions(tree, level = 0, excludeIds = []) {
                        tree.forEach(folder => {
                            if (!excludeIds.includes(folder.id) && !folderIds.includes(String(folder.id))) {
                                const indent = '&nbsp;'.repeat(level * 4);
                                options += `<option value="${folder.id}">${indent}${folder.name}</option>`;
                                if (folder.subfolders && folder.subfolders.length > 0) {
                                    buildOptions(folder.subfolders, level + 1, excludeIds);
                                }
                            }
                        });
                    }
                    
                    buildOptions(response.tree, 0, folderIds);
                    
                    Swal.fire({
                        title: 'Move Folders',
                        html: `
                            <p>Select new parent folder for <strong>${folderIds.length}</strong> selected folder(s):</p>
                            <select id="bulkMoveFolderParentSelect" class="form-select">
                                ${options}
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Move',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            $('#bulkMoveFolderParentSelect').select2({
                                dropdownParent: Swal.getContainer()
                            });
                        },
                        preConfirm: () => {
                            return $('#bulkMoveFolderParentSelect').val();
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const newParentId = result.value;
                            
                            Swal.fire({
                                title: 'Moving...',
                                text: 'Please wait while we move the folders',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            
                            $.ajax({
                                url: '{{ route("modules.files.digital.ajax") }}',
                                method: 'POST',
                                data: {
                                    _token: csrfToken,
                                    action: 'bulk_move_folders',
                                    folder_ids: folderIds,
                                    new_parent_id: newParentId
                                },
                                success: function(response) {
                                    Swal.close();
                                    if (response.success) {
                                        Swal.fire('Moved!', response.message, 'success');
                                        deselectAllFolders();
                                        loadFolderTree();
                                        loadFolderContents(currentFolderId);
                                    } else {
                                        Swal.fire('Error!', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close();
                                    const response = xhr.responseJSON;
                                    Swal.fire('Error!', response?.message || 'Failed to move folders', 'error');
                                }
                            });
                        }
                    });
                }
            }
        });
    };

    // Bulk Assign Folders to Staff
    window.bulkAssignFolders = function() {
        const folderIds = getSelectedFolderIds();
        
        if (folderIds.length === 0) {
            Swal.fire('Warning!', 'Please select at least one folder to assign', 'warning');
            return;
        }
        
        // Load users
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_users_for_assignment'
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Staff Member</option>';
                    response.users.forEach(user => {
                        options += `<option value="${user.id}">${user.name}</option>`;
                    });
                    
                    Swal.fire({
                        title: 'Assign Folders to Staff',
                        html: `
                            <p>Select staff member for <strong>${folderIds.length}</strong> selected folder(s):</p>
                            <select id="bulkAssignStaffSelect" class="form-select">
                                ${options}
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Assign',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            $('#bulkAssignStaffSelect').select2({
                                dropdownParent: Swal.getContainer()
                            });
                        },
                        preConfirm: () => {
                            const userId = $('#bulkAssignStaffSelect').val();
                            if (!userId) {
                                Swal.showValidationMessage('Please select a staff member');
                                return false;
                            }
                            return userId;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const userId = result.value;
                            
                            Swal.fire({
                                title: 'Assigning...',
                                text: 'Please wait while we assign the folders',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            
                            $.ajax({
                                url: '{{ route("modules.files.digital.ajax") }}',
                                method: 'POST',
                                data: {
                                    _token: csrfToken,
                                    action: 'bulk_assign_folders_to_staff',
                                    folder_ids: folderIds,
                                    user_id: userId
                                },
                                success: function(response) {
                                    Swal.close();
                                    if (response.success) {
                                        Swal.fire('Assigned!', response.message, 'success');
                                        deselectAllFolders();
                                        loadFolderTree();
                                        loadFolderContents(currentFolderId);
                                    } else {
                                        Swal.fire('Error!', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close();
                                    const response = xhr.responseJSON;
                                    Swal.fire('Error!', response?.message || 'Failed to assign folders', 'error');
                                }
                            });
                        }
                    });
                }
            }
        });
    };

    // Bulk Assign Folders to Department
    window.bulkAssignFoldersToDept = function() {
        const folderIds = getSelectedFolderIds();
        
        if (folderIds.length === 0) {
            Swal.fire('Warning!', 'Please select at least one folder to assign', 'warning');
            return;
        }
        
        // Load departments
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_departments'
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Department</option>';
                    response.departments.forEach(dept => {
                        options += `<option value="${dept.id}">${dept.name}</option>`;
                    });
                    
                    Swal.fire({
                        title: 'Assign Folders to Department',
                        html: `
                            <p>Select department for <strong>${folderIds.length}</strong> selected folder(s):</p>
                            <select id="bulkAssignDeptSelect" class="form-select">
                                ${options}
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Assign',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            $('#bulkAssignDeptSelect').select2({
                                dropdownParent: Swal.getContainer()
                            });
                        },
                        preConfirm: () => {
                            const deptId = $('#bulkAssignDeptSelect').val();
                            if (!deptId) {
                                Swal.showValidationMessage('Please select a department');
                                return false;
                            }
                            return deptId;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const deptId = result.value;
                            
                            Swal.fire({
                                title: 'Assigning...',
                                text: 'Please wait while we assign the folders',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            
                            $.ajax({
                                url: '{{ route("modules.files.digital.ajax") }}',
                                method: 'POST',
                                data: {
                                    _token: csrfToken,
                                    action: 'bulk_assign_folders_to_department',
                                    folder_ids: folderIds,
                                    department_id: deptId
                                },
                                success: function(response) {
                                    Swal.close();
                                    if (response.success) {
                                        Swal.fire('Assigned!', response.message, 'success');
                                        deselectAllFolders();
                                        loadFolderTree();
                                        loadFolderContents(currentFolderId);
                                    } else {
                                        Swal.fire('Error!', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close();
                                    const response = xhr.responseJSON;
                                    Swal.fire('Error!', response?.message || 'Failed to assign folders', 'error');
                                }
                            });
                        }
                    });
                }
            }
        });
    };

    // Initialize Application
    initApplication();
});
</script>
@endpush