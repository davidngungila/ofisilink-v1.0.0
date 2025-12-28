@extends('layouts.app')

@section('title', 'Asset Categories')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Asset Categories</h4>
    <ul class="db-breadcrumb-list">
        <li><a href="{{ route('modules.accounting.index') }}"><i class="fa fa-home"></i>Accounting</a></li>
        <li><a href="{{ route('modules.accounting.fixed-assets.index') }}">Fixed Assets</a></li>
        <li>Categories</li>
    </ul>
</div>
@endsection

@push('styles')
<style>
    .category-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .category-card.active { border-left-color: #28a745; }
    .category-card.inactive { border-left-color: #6c757d; }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
    }
    .stat-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
@php
    $totalCategories = $categories->count();
    $activeCategories = $categories->where('is_active', true)->count();
    $totalAssets = $categories->sum(fn($cat) => $cat->assets()->count());
    $totalValue = $categories->sum(function($cat) {
        return $cat->assets()->sum('total_cost');
    });
@endphp
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="mb-2">Total Categories</h6>
                <h2 class="mb-0">{{ $totalCategories }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <h6 class="mb-2">Active Categories</h6>
                <h2 class="mb-0">{{ $activeCategories }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body text-center">
                <h6 class="mb-2">Total Assets</h6>
                <h2 class="mb-0">{{ $totalAssets }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body text-center">
                <h6 class="mb-2">Total Asset Value</h6>
                <h2 class="mb-0">TZS {{ number_format($totalValue, 0) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Categories Grid View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tags"></i> Asset Categories Management
                </h5>
                <div>
                    <button class="btn btn-sm btn-secondary" id="toggleView" title="Toggle View">
                        <i class="fas fa-th"></i> <span id="viewText">Grid</span>
                    </button>
                    <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Assets
                    </a>
                    <button class="btn btn-sm btn-primary" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i> New Category
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchCategories" placeholder="Search categories...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterMethod">
                            <option value="">All Methods</option>
                            <option value="Straight Line">Straight Line</option>
                            <option value="Declining Balance">Declining Balance</option>
                            <option value="Sum of Years Digits">Sum of Years Digits</option>
                            <option value="Units of Production">Units of Production</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-block" onclick="clearFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="row">
                    @forelse($categories as $category)
                    @php
                        $assetCount = $category->assets()->count();
                        $categoryValue = $category->assets()->sum('total_cost');
                    @endphp
                    <div class="col-md-4 mb-3 category-item" 
                         data-name="{{ strtolower($category->name) }}"
                         data-status="{{ $category->is_active ? 'active' : 'inactive' }}"
                         data-method="{{ $category->depreciation_method }}">
                        <div class="card category-card {{ $category->is_active ? 'active' : 'inactive' }}" onclick="editCategory({{ $category->id }})">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <code>{{ $category->code }}</code> - {{ $category->name }}
                                        </h5>
                                        @if($category->description)
                                        <p class="text-muted small mb-0">{{ Str::limit($category->description, 60) }}</p>
                                        @endif
                                    </div>
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Assets</small>
                                        <strong class="text-primary">{{ $assetCount }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Value</small>
                                        <strong class="text-success">TZS {{ number_format($categoryValue, 0) }}</strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="small">
                                    <div class="mb-1">
                                        <strong>Method:</strong> {{ $category->depreciation_method }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>Rate:</strong> {{ $category->default_depreciation_rate }}% | 
                                        <strong>Life:</strong> {{ $category->default_useful_life_years }}yrs
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-info btn-block" onclick="event.stopPropagation(); editCategory({{ $category->id }})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p class="mb-0">No categories found. Click "New Category" to create one.</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Table View (Hidden by default) -->
                <div id="tableView" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Depreciation Method</th>
                                    <th>Default Rate</th>
                                    <th>Useful Life</th>
                                    <th>Assets Count</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                @php
                                    $assetCount = $category->assets()->count();
                                    $categoryValue = $category->assets()->sum('total_cost');
                                @endphp
                                <tr class="category-item"
                                    data-name="{{ strtolower($category->name) }}"
                                    data-status="{{ $category->is_active ? 'active' : 'inactive' }}"
                                    data-method="{{ $category->depreciation_method }}">
                                    <td><code>{{ $category->code }}</code></td>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>{{ $category->depreciation_method }}</td>
                                    <td>{{ $category->default_depreciation_rate }}%</td>
                                    <td>{{ $category->default_useful_life_years }} years</td>
                                    <td>
                                        <span class="badge badge-info">{{ $assetCount }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">TZS {{ number_format($categoryValue, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="editCategory({{ $category->id }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory({{ $category->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-info-circle text-muted"></i>
                                        <p class="text-muted mb-0">No categories found. Click "New Category" to create one.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">New Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Category Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required>
                                <small class="form-text text-muted">Unique code (e.g., VEH, IT, FURN) - Used for barcode prefixes</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="depreciation_method">Depreciation Method <span class="text-danger">*</span></label>
                                <select class="form-control" id="depreciation_method" name="depreciation_method" required>
                                    <option value="Straight Line">Straight Line</option>
                                    <option value="Declining Balance">Declining Balance</option>
                                    <option value="Sum of Years Digits">Sum of Years Digits</option>
                                    <option value="Units of Production">Units of Production</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="default_depreciation_rate">Default Depreciation Rate (%)</label>
                                <input type="number" class="form-control" id="default_depreciation_rate" name="default_depreciation_rate" step="0.01" min="0" max="100">
                                <small class="form-text text-muted">Leave empty for auto-calculation</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="default_useful_life_years">Default Useful Life (Years) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="default_useful_life_years" name="default_useful_life_years" min="1" max="100" value="5" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6>Accounting Integration (Optional)</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="asset_account_id">Asset Account</label>
                                <select class="form-control" id="asset_account_id" name="asset_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($assetAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="depreciation_expense_account_id">Depreciation Expense Account</label>
                                <select class="form-control" id="depreciation_expense_account_id" name="depreciation_expense_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($expenseAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="accumulated_depreciation_account_id">Accumulated Depreciation Account</label>
                                <select class="form-control" id="accumulated_depreciation_account_id" name="accumulated_depreciation_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($assetAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let editingCategoryId = null;
let isGridView = true;

function openCategoryModal() {
    // Clean up any existing modals
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        editingCategoryId = null;
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#categoryModalTitle').text('New Category');
        $('#is_active').prop('checked', true);
        
        // Show modal - try Bootstrap 5 first, fallback to jQuery
        const modalElement = document.getElementById('categoryModal');
        if (modalElement) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                // Bootstrap 5
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
                
                // Ensure modal is responsive after show
                modalElement.addEventListener('shown.bs.modal', function() {
                    $(this).css('z-index', 1050);
                    $('.modal-backdrop').css('z-index', 1040);
                    $(this).find('.modal-content').css('pointer-events', 'auto');
                }, { once: true });
            } else {
                // Fallback to jQuery/Bootstrap 4
                $('#categoryModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            }
        }
    }, 100);
}

function editCategory(id) {
    editingCategoryId = id;
    $.ajax({
        url: `/modules/accounting/fixed-assets/categories/${id}`,
        method: 'GET',
        success: function(response) {
            const category = response.category || response;
            $('#categoryId').val(category.id);
            $('#code').val(category.code);
            $('#name').val(category.name);
            $('#description').val(category.description);
            $('#depreciation_method').val(category.depreciation_method);
            $('#default_depreciation_rate').val(category.default_depreciation_rate);
            $('#default_useful_life_years').val(category.default_useful_life_years);
            $('#asset_account_id').val(category.asset_account_id);
            $('#depreciation_expense_account_id').val(category.depreciation_expense_account_id);
            $('#accumulated_depreciation_account_id').val(category.accumulated_depreciation_account_id);
            $('#is_active').prop('checked', category.is_active);
            $('#categoryModalTitle').text('Edit Category');
            
            // Show modal - try Bootstrap 5 first, fallback to jQuery
            const modalElement = document.getElementById('categoryModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    // Bootstrap 5
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                    
                    // Ensure modal is responsive after show
                    modalElement.addEventListener('shown.bs.modal', function() {
                        $(this).css('z-index', 1050);
                        $('.modal-backdrop').css('z-index', 1040);
                        $(this).find('.modal-content').css('pointer-events', 'auto');
                    }, { once: true });
                } else {
                    // Fallback to jQuery/Bootstrap 4
                    $('#categoryModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            }
        },
        error: function() {
            alert('Please refresh the page and try again.');
        }
    });
}

function deleteCategory(id) {
    if (!confirm('Are you sure you want to delete this category? This action cannot be undone if there are assets assigned to it.')) {
        return;
    }

    $.ajax({
        url: `/modules/accounting/fixed-assets/categories/${id}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Error deleting category';
            alert(error);
        }
    });
}

function filterCategories() {
    const searchTerm = $('#searchCategories').val().toLowerCase();
    const statusFilter = $('#filterStatus').val();
    const methodFilter = $('#filterMethod').val();

    $('.category-item').each(function() {
        const name = $(this).data('name') || '';
        const status = $(this).data('status') || '';
        const method = $(this).data('method') || '';

        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesMethod = !methodFilter || method === methodFilter;

        if (matchesSearch && matchesStatus && matchesMethod) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function clearFilters() {
    $('#searchCategories').val('');
    $('#filterStatus').val('');
    $('#filterMethod').val('');
    filterCategories();
}

function toggleView() {
    isGridView = !isGridView;
    if (isGridView) {
        $('#gridView').show();
        $('#tableView').hide();
        $('#viewText').text('Grid');
        $('#toggleView i').removeClass('fa-list').addClass('fa-th');
    } else {
        $('#gridView').hide();
        $('#tableView').show();
        $('#viewText').text('Table');
        $('#toggleView i').removeClass('fa-th').addClass('fa-list');
    }
}

$('#categoryForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        code: $('#code').val(),
        name: $('#name').val(),
        description: $('#description').val(),
        depreciation_method: $('#depreciation_method').val(),
        default_depreciation_rate: $('#default_depreciation_rate').val() || null,
        default_useful_life_years: $('#default_useful_life_years').val(),
        asset_account_id: $('#asset_account_id').val() || null,
        depreciation_expense_account_id: $('#depreciation_expense_account_id').val() || null,
        accumulated_depreciation_account_id: $('#accumulated_depreciation_account_id').val() || null,
        is_active: $('#is_active').is(':checked')
    };

    const url = editingCategoryId 
        ? `/modules/accounting/fixed-assets/categories/${editingCategoryId}`
        : '/modules/accounting/fixed-assets/categories';
    const method = editingCategoryId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                const modalElement = document.getElementById('categoryModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            let errorMessage = xhr.responseJSON?.message || 'Error saving category';
            
            if (Object.keys(errors).length > 0) {
                errorMessage = Object.values(errors).flat().join('\n');
            }
            
            alert(errorMessage);
        }
    });
});

// Event listeners
$('#searchCategories').on('input', filterCategories);
$('#filterStatus, #filterMethod').on('change', filterCategories);
$('#toggleView').on('click', toggleView);
</script>
@endpush
