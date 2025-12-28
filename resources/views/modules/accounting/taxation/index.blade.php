@extends('layouts.app')

@section('title', 'Tax Settings')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Tax Settings</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="total"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="active"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="inactive"] {
        border-left-color: #6c757d !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .tax-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .tax-type-VAT { background-color: #e3f2fd; color: #1976d2; }
    .tax-type-GST { background-color: #f3e5f5; color: #7b1fa2; }
    .tax-type-Withholding\ Tax { background-color: #fff3e0; color: #e65100; }
    .tax-type-PAYE { background-color: #e8f5e9; color: #388e3c; }
    .tax-type-Corporate\ Tax { background-color: #fce4ec; color: #c2185b; }
    .tax-type-Other { background-color: #f5f5f5; color: #616161; }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-calculator me-2"></i>Tax Settings Management
                    </h2>
                    <p class="mb-0 opacity-90">Configure and manage tax settings for your organization</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('modules.accounting.taxation.reports') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i>Tax Reports
                    </a>
                    <button class="btn btn-light btn-sm" onclick="openTaxModal()" title="New Tax Setting">
                        <i class="bx bx-plus me-1"></i>New Tax Setting
                    </button>
                    <button class="btn btn-light btn-sm" id="btn-refresh" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="total">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Settings</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Tax configurations</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-list-ul fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="active">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Active Settings</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumActive">0</h3>
                            <small class="text-muted">Currently active</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="inactive">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Inactive Settings</h6>
                            <h3 class="mb-0 text-secondary fw-bold" id="sumInactive">0</h3>
                            <small class="text-muted">Currently inactive</small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-x-circle fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-filter-alt me-2"></i>Filters & Search
                </h6>
                <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" id="toggleFilters">
                    <i class="bx bx-chevron-up" id="filterIcon"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Tax Type</label>
                        <select class="form-select form-select-sm" id="filterTaxType">
                            <option value="">All Tax Types</option>
                            <option value="VAT">VAT</option>
                            <option value="GST">GST</option>
                            <option value="Withholding Tax">Withholding Tax</option>
                            <option value="PAYE">PAYE</option>
                            <option value="Corporate Tax">Corporate Tax</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by name, code, description...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btn-apply-filters">
                            <i class="bx bx-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Tax Settings
                    <span class="badge bg-primary ms-2" id="settingsCount">0</span>
                </h6>
                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                    <label class="small text-muted me-2">Per page:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0" id="taxSettingsTable">
                    <thead class="table-light">
                            <tr>
                                <th>Tax Name</th>
                                <th>Code</th>
                                <th>Type</th>
                            <th class="text-end">Rate (%)</th>
                            <th>Account</th>
                                <th>Status</th>
                            <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    <tbody id="taxSettingsTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading tax settings...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 settings
                </div>
                <nav aria-label="Tax settings pagination">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Tax Setting Modal -->
<div class="modal fade" id="taxSettingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taxSettingModalTitle">New Tax Setting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="taxSettingForm">
                <div class="modal-body">
                    <input type="hidden" id="taxSettingId" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tax_name" name="tax_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tax_code" name="tax_code" required>
                            <small class="form-text text-muted">Unique identifier for this tax</small>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="tax_type" name="tax_type" required>
                                <option value="">Select Tax Type</option>
                                <option value="VAT">VAT (Value Added Tax)</option>
                                <option value="GST">GST (Goods and Services Tax)</option>
                                <option value="Withholding Tax">Withholding Tax</option>
                                <option value="PAYE">PAYE (Pay As You Earn)</option>
                                <option value="Corporate Tax">Corporate Tax</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="rate" name="rate" step="0.01" min="0" max="100" required>
                            <small class="form-text text-muted">Enter percentage (e.g., 18 for 18%)</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Liability Account</label>
                        <select class="form-select" id="account_id" name="account_id">
                            <option value="">Select Account (Optional)</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Chart of account to track this tax liability</small>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mt-3">
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
                        <i class="fas fa-save"></i> Save Tax Setting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Advanced Tax Settings Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.taxation.data') }}';
    const storeEndpoint = '{{ route('modules.accounting.taxation.store') }}';
    let page = 1, perPage = 20;
    let allSettings = [];
    let editingTaxId = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            tax_type: document.getElementById('filterTaxType')?.value || '',
            is_active: document.getElementById('filterStatus')?.value || '',
            q: document.getElementById('filterQ')?.value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const total = summary?.total_settings || 0;
        const active = summary?.active_settings || 0;
        const inactive = summary?.inactive_settings || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumActive', 0, active, 800);
        animateValue('sumInactive', 0, inactive, 800);
        
        if(document.getElementById('settingsCount')) document.getElementById('settingsCount').textContent = total.toLocaleString();
    }
    
    function animateValue(id, start, end, duration){
        const element = document.getElementById(id);
        if(!element) return;
        const startVal = parseInt(element.textContent) || start;
        let current = startVal;
        const range = end - startVal;
        const increment = range / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                element.textContent = Math.round(end).toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.round(current).toLocaleString();
            }
        }, 16);
    }
    
    function escapeHtml(s){ 
        return (s||'').replace(/[&<>"']/g, m => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            '\'':'&#39;'
        }[m])); 
    }
    
    function getTaxTypeClass(type){
        return 'tax-type-' + (type || 'Other').replace(/\s+/g, '\\ ');
    }
    
    function renderTable(settings){
        const tbody = document.getElementById('taxSettingsTableBody');
        if(!tbody) return;
        
        if(!settings || !settings.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No tax settings found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = settings.map((s, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td>
                    <div class="fw-medium">${escapeHtml(s.tax_name || 'N/A')}</div>
                    ${s.description ? `<small class="text-muted">${escapeHtml(s.description.substring(0, 50))}${s.description.length > 50 ? '...' : ''}</small>` : ''}
                </td>
                <td><code class="text-primary">${escapeHtml(s.tax_code || '')}</code></td>
                <td>
                    <span class="tax-type-badge ${getTaxTypeClass(s.tax_type)}">
                        ${escapeHtml(s.tax_type || '')}
                    </span>
                </td>
                <td class="text-end">
                    <strong>${number(s.rate)}%</strong>
                </td>
                <td>
                    ${s.account_code && s.account_name ? `<small>${escapeHtml(s.account_code)} - ${escapeHtml(s.account_name)}</small>` : '<span class="text-muted">Not assigned</span>'}
                </td>
                <td>
                    <span class="badge ${s.is_active ? 'bg-success' : 'bg-secondary'}">
                        ${s.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="editTaxSetting(${s.id})" title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteTaxSetting(${s.id})" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalSettings){
        const totalPages = Math.ceil(totalSettings / perPage);
        const pagination = document.getElementById('pagination');
        if(!pagination) return;
        
        if(totalPages <= 1){
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;
        
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if(startPage > 1){
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if(startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        
        for(let i = startPage; i <= endPage; i++){
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        if(endPage < totalPages){
            if(endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;
        
        pagination.innerHTML = html;
        
        pagination.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                page = parseInt(link.dataset.page);
                load();
            });
        });
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('taxSettingsTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading tax settings...</p>
                </td>
            </tr>
        `;
        
        fetch(endpoint, {
            method:'POST', 
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Accept':'application/json'
            }, 
            body: JSON.stringify(qs())
        })
        .then(async r=>{
            const text = await r.text();
            let res;
            try { 
                res = JSON.parse(text); 
            } catch(e) { 
                console.error('JSON parse error:', text); 
                throw new Error('Invalid response from server'); 
            }
            
            if(!r.ok || !res.success){ 
                let msg = res.message || 'Failed to load tax settings';
                
                if (res.errors) {
                    const errorMessages = [];
                    Object.keys(res.errors).forEach(key => {
                        if (Array.isArray(res.errors[key])) {
                            res.errors[key].forEach(err => {
                                errorMessages.push(`${key}: ${err}`);
                            });
                        } else {
                            errorMessages.push(`${key}: ${res.errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        msg = 'Validation Errors:<br>' + errorMessages.join('<br>');
                    }
                }
                
                const errorHtml = msg.includes('<br>') ? msg : escapeHtml(msg);
                body.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_settings: 0, active_settings: 0, inactive_settings: 0 });
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 settings';
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allSettings = res.tax_settings || [];
            updateSummary(res.summary || {});
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} settings`;
            
            renderTable(allSettings);
            renderPagination(page, res.summary?.count || 0);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(body) {
                body.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ total_settings: 0, active_settings: 0, inactive_settings: 0 });
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    if(document.getElementById('btn-refresh')) {
        document.getElementById('btn-refresh').addEventListener('click', () => { 
            page = 1; 
            load(); 
        });
    }
    
    if(document.getElementById('btn-apply-filters')) {
        document.getElementById('btn-apply-filters').addEventListener('click', () => { 
            page = 1; 
            load(); 
        });
    }
    
    ['filterTaxType', 'filterStatus'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('change', () => { page = 1; load(); });
    });
    
    if(document.getElementById('filterQ')) {
        document.getElementById('filterQ').addEventListener('input', () => { 
            page = 1; 
            debounce(load, 300)(); 
        });
    }
    
    if(document.getElementById('clearSearch')) {
        document.getElementById('clearSearch').addEventListener('click', () => {
            if(document.getElementById('filterQ')) document.getElementById('filterQ').value = '';
            page = 1;
            load();
        });
    }
    
    if(document.getElementById('perPageSelect')) {
        document.getElementById('perPageSelect').addEventListener('change', function(){
            perPage = parseInt(this.value);
            page = 1;
            load();
        });
    }
    
    // Filter collapse toggle
    if(document.getElementById('toggleFilters')) {
        document.getElementById('toggleFilters').addEventListener('click', function(){
            const icon = document.getElementById('filterIcon');
            if(icon) {
                const isCollapsed = document.getElementById('filterCollapse')?.classList.contains('show');
                icon.className = isCollapsed ? 'bx bx-chevron-down' : 'bx bx-chevron-up';
            }
        });
    }
    
    let t = null;
    function debounce(fn, ms){ 
        return () => { 
            clearTimeout(t); 
            t = setTimeout(fn, ms); 
        }; 
    }
    
    // Tax Modal Functions
    window.openTaxModal = function() {
        editingTaxId = null;
        const form = document.getElementById('taxSettingForm');
        if(form) form.reset();
        if(document.getElementById('taxSettingId')) document.getElementById('taxSettingId').value = '';
        if(document.getElementById('taxSettingModalTitle')) document.getElementById('taxSettingModalTitle').textContent = 'New Tax Setting';
        if(document.getElementById('is_active')) document.getElementById('is_active').checked = true;
        const modal = new bootstrap.Modal(document.getElementById('taxSettingModal'));
        modal.show();
    };
    
    window.editTaxSetting = function(id) {
        editingTaxId = id;
        fetch(`{{ url('/modules/accounting/taxation') }}/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const tax = data.taxSetting || data;
            if(document.getElementById('taxSettingId')) document.getElementById('taxSettingId').value = tax.id;
            if(document.getElementById('tax_name')) document.getElementById('tax_name').value = tax.tax_name || '';
            if(document.getElementById('tax_code')) document.getElementById('tax_code').value = tax.tax_code || '';
            if(document.getElementById('tax_type')) document.getElementById('tax_type').value = tax.tax_type || '';
            if(document.getElementById('rate')) document.getElementById('rate').value = tax.rate || '';
            if(document.getElementById('account_id')) document.getElementById('account_id').value = tax.account_id || '';
            if(document.getElementById('description')) document.getElementById('description').value = tax.description || '';
            if(document.getElementById('is_active')) document.getElementById('is_active').checked = tax.is_active ?? true;
            if(document.getElementById('taxSettingModalTitle')) document.getElementById('taxSettingModalTitle').textContent = 'Edit Tax Setting';
            const modal = new bootstrap.Modal(document.getElementById('taxSettingModal'));
            modal.show();
        })
        .catch(err => {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Failed to load tax setting details');
            } else {
                alert('Failed to load tax setting details');
            }
        });
    };
    
    window.deleteTaxSetting = function(id) {
        if (!confirm('Are you sure you want to delete this tax setting?')) return;
        
        fetch(`{{ url('/modules/accounting/taxation') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', data.message || 'Tax setting deleted successfully');
                } else {
                    alert(data.message || 'Tax setting deleted successfully');
                }
                load();
            } else {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', data.message || 'Error deleting tax setting');
                } else {
                    alert(data.message || 'Error deleting tax setting');
                }
            }
        })
        .catch(err => {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Network error');
            } else {
                alert('Network error');
            }
        });
    };
    
    // Form Submission
    if(document.getElementById('taxSettingForm')) {
        document.getElementById('taxSettingForm').addEventListener('submit', function(e){
            e.preventDefault();
            
            const formData = {
                tax_name: document.getElementById('tax_name').value,
                tax_code: document.getElementById('tax_code').value,
                tax_type: document.getElementById('tax_type').value,
                rate: document.getElementById('rate').value,
                account_id: document.getElementById('account_id').value || null,
                description: document.getElementById('description').value,
                is_active: document.getElementById('is_active').checked
            };
            
            const url = editingTaxId 
                ? `{{ url('/modules/accounting/taxation') }}/${editingTaxId}`
                : storeEndpoint;
            const method = editingTaxId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', data.message || 'Tax setting saved successfully');
                    } else {
                        alert(data.message || 'Tax setting saved successfully');
                    }
                    bootstrap.Modal.getInstance(document.getElementById('taxSettingModal')).hide();
                    setTimeout(() => load(), 1500);
                } else {
                    let msg = data.message || 'Error saving tax setting';
                    if (data.errors) {
                        const errors = [];
                        Object.keys(data.errors).forEach(key => {
                            if (Array.isArray(data.errors[key])) {
                                data.errors[key].forEach(err => errors.push(`${key}: ${err}`));
                            } else {
                                errors.push(`${key}: ${data.errors[key]}`);
                            }
                        });
                        if (errors.length > 0) msg = errors.join(' | ');
                    }
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', msg);
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(err => {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', err.message || 'Network error');
                } else {
                    alert(err.message || 'Network error');
                }
            });
        });
    }
    
    // Make load function globally accessible
    window.loadTaxSettings = function(resetPage = false) {
        if (resetPage) {
            page = 1;
        }
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
