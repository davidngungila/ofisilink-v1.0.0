@extends('layouts.app')

@section('title', 'Search Physical Files')

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
                                <i class="bx bx-search me-2"></i>Search Physical Files
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Advanced search with filters to find physical files and racks quickly
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="search-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Search Term</label>
                                <input type="text" class="form-control" id="search-term" name="search_term" placeholder="Enter file name, description, or rack name...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rack Folder</label>
                                <select class="form-select" id="rack-filter" name="rack_id">
                                    <option value="">All Racks</option>
                                    @foreach($rackFolders as $rack)
                                        <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" id="category-filter" name="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="department-filter" name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">File Status</label>
                                <select class="form-select" id="status-filter" name="status">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="issued">Issued</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date-from" name="date_from">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date-to" name="date_to">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-list-ul me-2"></i>Search Results
                    </h5>
                </div>
                <div class="card-body">
                    <div id="search-results">
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-search fs-1 mb-3"></i>
                            <p>Enter search criteria and click Search to find files and racks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    let searchTimeout;
    
    // Live search as user types
    $('#search-term').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        
        if (searchTerm.length >= 2) {
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 500);
        } else if (searchTerm.length === 0) {
            $('#search-results').html('<div class="text-center py-5 text-muted"><i class="bx bx-search fs-1 mb-3"></i><p>Enter search criteria and click Search to find files and racks</p></div>');
        }
    });
    
    // Search on form submit
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    function performSearch() {
        const formData = $('#search-form').serialize();
        
        $('#search-results').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>');
        
        $.ajax({
            url: '{{ route("modules.files.physical.ajax") }}',
            type: 'POST',
            data: {
                action: 'search_rack_files',
                _token: '{{ csrf_token() }}',
                ...Object.fromEntries(new URLSearchParams(formData))
            },
            success: function(response) {
                if (response.success && ((response.files && response.files.length > 0) || (response.racks && response.racks.length > 0))) {
                    displaySearchResults(response.files || [], response.racks || []);
                } else {
                    $('#search-results').html('<div class="alert alert-info"><i class="bx bx-info-circle me-2"></i>No files or racks found matching your criteria.</div>');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                $('#search-results').html(`<div class="alert alert-danger"><i class="bx bx-error-circle me-2"></i>${response?.message || 'An error occurred while searching.'}</div>`);
            }
        });
    }

    function displaySearchResults(files, racks) {
        let html = '';
        const totalCount = (files.length || 0) + (racks.length || 0);
        
        if (racks.length > 0) {
            html += '<h6 class="mb-3"><i class="bx bx-archive me-2"></i>Racks (' + racks.length + ')</h6>';
            html += '<div class="table-responsive mb-4"><table class="table table-hover"><thead><tr><th>Name</th><th>Category</th><th>Department</th><th>Location</th><th>Files</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            
            racks.forEach(function(rack) {
                html += `
                    <tr>
                        <td><i class="bx bx-archive me-2 text-primary"></i><strong>${escapeHtml(rack.name || 'N/A')}</strong></td>
                        <td>${escapeHtml(rack.category_name || 'N/A')}</td>
                        <td>${escapeHtml(rack.department_name || 'N/A')}</td>
                        <td>${escapeHtml(rack.location || 'N/A')}</td>
                        <td><span class="badge bg-label-info">${rack.files_count || 0}</span></td>
                        <td>${rack.created_at || 'N/A'}</td>
                        <td>
                            <a href="/modules/files/physical/rack/${rack.id}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show"></i> View
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        if (files.length > 0) {
            html += '<h6 class="mb-3"><i class="bx bx-file me-2"></i>Files (' + files.length + ')</h6>';
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Rack</th><th>File Number</th><th>Type</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            
            files.forEach(function(file) {
                html += `
                    <tr>
                        <td><i class="bx bx-file me-2 text-success"></i><strong>${escapeHtml(file.file_name || 'N/A')}</strong></td>
                        <td><i class="bx bx-archive me-1"></i>${escapeHtml(file.rack_name || 'N/A')}</td>
                        <td>${escapeHtml(file.file_number || 'N/A')}</td>
                        <td><span class="badge bg-label-info">${escapeHtml(file.file_type || 'N/A')}</span></td>
                        <td><span class="badge bg-label-${file.status === 'issued' ? 'warning' : (file.status === 'archived' ? 'secondary' : 'success')}">${escapeHtml(file.status || 'N/A')}</span></td>
                        <td>${file.created_at || 'N/A'}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-file" data-file-id="${file.id || file.file_id}">
                                    <i class="bx bx-show"></i> View
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        html += `<div class="mt-3"><p class="text-muted"><strong>${totalCount}</strong> result(s) found (${racks.length} rack(s), ${files.length} file(s))</p></div>`;
        $('#search-results').html(html);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
@endsection


