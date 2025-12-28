@extends('layouts.app')

@section('title', 'Asset Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
      <h4 class="fw-bold mb-0"><i class="bx bx-package"></i> Asset Management</h4>
      <p class="text-muted mb-0">Manage asset categories, assets, assignments, issues and maintenance</p>
        </div>
        <div class="d-flex gap-2">
      <a href="{{ route('modules.finance.ledger') }}" class="btn btn-outline-secondary"><i class="bx bx-book"></i> Ledger</a>
      <a href="{{ route('finance.settings.index') }}" class="btn btn-outline-dark"><i class="bx bx-cog"></i> Finance Settings</a>
        </div>
    </div>

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabCategories" role="tab">Categories</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAssets" role="tab">Assets</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAssignments" role="tab">Assignments</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabIssues" role="tab">Issues & Maintenance</a></li>
                    </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabCategories">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Asset Categories</h6>
          <button class="btn btn-primary btn-sm" id="btnNewCat"><i class="bx bx-plus"></i> New Category</button>
                </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="tblCategories">
              <thead><tr><th>Code</th><th>Name</th><th>Depreciation</th><th>Status</th><th></th></tr></thead>
              <tbody></tbody>
            </table>
            </div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="tabAssets">
      <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Assets</h6>
          <button class="btn btn-primary btn-sm" id="btnNewAsset"><i class="bx bx-plus"></i> New Asset</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
            <table class="table table-sm table-hover" id="tblAssets">
              <thead><tr><th>Tag</th><th>Name</th><th>Category</th><th>Status</th><th>Assigned To</th><th></th></tr></thead>
              <tbody></tbody>
                        </table>
                    </div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="tabAssignments">
      <div class="alert alert-info">Assign/return assets from the Assets tab (per item).</div>
    </div>
    <div class="tab-pane fade" id="tabIssues">
      <!-- Statistics Cards -->
      <div class="row mb-4" id="issueStatsCards">
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body">
              <h5 class="mb-0" id="statTotal">0</h5>
              <small>Total Issues</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-white">
            <div class="card-body">
              <h5 class="mb-0" id="statReported">0</h5>
              <small>Reported</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h5 class="mb-0" id="statInProgress">0</h5>
              <small>In Progress</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-success text-white">
            <div class="card-body">
              <h5 class="mb-0" id="statResolved">0</h5>
              <small>Resolved</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h6 class="mb-0"><i class="bx bx-error-circle"></i> Asset Issues & Maintenance</h6>
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-primary" id="btnReportIssue"><i class="bx bx-plus"></i> Report New Issue</button>
            <button class="btn btn-sm btn-success" id="btnBulkActions" disabled><i class="bx bx-check-square"></i> Bulk Actions</button>
            <button class="btn btn-sm btn-outline-primary" id="btnExportIssues"><i class="bx bx-download"></i> Export</button>
          </div>
        </div>
        <div class="card-body">
          <!-- Filters -->
          <div class="row g-2 mb-3">
            <div class="col-md-2">
              <label class="form-label small">Status</label>
              <select class="form-select form-select-sm" id="filterStatus">
                <option value="">All Status</option>
                <option value="reported">Reported</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small">Priority</label>
              <select class="form-select form-select-sm" id="filterPriority">
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small">Type</label>
              <select class="form-select form-select-sm" id="filterType">
                <option value="">All Types</option>
                <option value="maintenance">Maintenance</option>
                <option value="damage">Damage</option>
                <option value="loss">Loss</option>
                <option value="theft">Theft</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small">Date From</label>
              <input type="date" class="form-control form-control-sm" id="filterDateFrom">
            </div>
            <div class="col-md-2">
              <label class="form-label small">Date To</label>
              <input type="date" class="form-control form-control-sm" id="filterDateTo">
            </div>
            <div class="col-md-2">
              <label class="form-label small">&nbsp;</label>
              <button class="btn btn-sm btn-outline-secondary w-100" id="btnApplyFilters"><i class="bx bx-filter"></i> Apply</button>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-md-6">
              <input type="text" class="form-control form-control-sm" id="searchIssues" placeholder="Search issues by title, asset tag, or description...">
            </div>
            <div class="col-md-6 text-end">
              <span class="text-muted small" id="issuesCount">0 issues found</span>
            </div>
          </div>

          <!-- Issues Table -->
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="tblIssues">
              <thead>
                <tr>
                  <th width="30"><input type="checkbox" id="selectAllIssues"></th>
                  <th>ID</th>
                  <th>Asset</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Reported By</th>
                  <th>Assigned To</th>
                  <th>Reported Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="issuesPagination" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Category Modal (prompt-like quick modal)-->
  <div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="catModalTitle">New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="catForm">
            <input type="hidden" id="catId">
            <div class="mb-2"><label class="form-label">Code</label><input class="form-control" id="catCode" required></div>
            <div class="mb-2"><label class="form-label">Name</label><input class="form-control" id="catName" required></div>
            <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control" id="catDesc"></textarea></div>
            <div class="row g-2">
              <div class="col"><label class="form-label">Years</label><input type="number" min="1" max="50" class="form-control" id="catYears" required></div>
              <div class="col"><label class="form-label">Rate %</label><input type="number" min="0" max="100" step="0.01" class="form-control" id="catRate" required></div>
              <div class="col d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="catActive" checked><label class="form-check-label">Active</label></div></div>
            </div>
          </form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="btnSaveCat">Save</button></div>
      </div>
    </div>
                </div>

  <!-- Asset Modal -->
  <div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="assetModalTitle">New Asset</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="assetForm">
            <input type="hidden" id="assetId">
            <div class="row g-2">
              <div class="col-md-3"><label class="form-label">Tag</label><input class="form-control" id="assetTag" required></div>
              <div class="col-md-5"><label class="form-label">Name</label><input class="form-control" id="assetName" required></div>
              <div class="col-md-4"><label class="form-label">Category</label><select class="form-select" id="assetCategory" required></select></div>
              <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" id="assetStatus"><option>available</option><option>assigned</option><option>maintenance</option><option>disposed</option><option>lost</option></select></div>
              <div class="col-md-4"><label class="form-label">Condition</label><select class="form-select" id="assetCondition"><option>excellent</option><option>good</option><option>fair</option><option>poor</option><option>damaged</option></select></div>
              <div class="col-md-4"><label class="form-label">Purchase Date</label><input type="date" class="form-control" id="assetPurchaseDate"></div>
              <div class="col-md-4"><label class="form-label">Price</label><input type="number" step="0.01" class="form-control" id="assetPrice"></div>
              <div class="col-md-4"><label class="form-label">Warranty Expiry</label><input type="date" class="form-control" id="assetWarranty"></div>
              <div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" id="assetDesc"></textarea></div>
            </div>
          </form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="btnSaveAsset">Save</button></div>
            </div>
        </div>
    </div>

  <!-- Issue Report Modal -->
  <div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="issueModalTitle">Report New Issue</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="issueForm">
            <input type="hidden" id="issueId">
            <input type="hidden" id="issueAssetId">
            <div class="mb-3">
              <label class="form-label">Asset <span class="text-danger">*</span></label>
              <select class="form-select" id="issueAsset" required></select>
            </div>
            <div class="mb-3">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="issueTitle" required>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label">Issue Type <span class="text-danger">*</span></label>
                <select class="form-select" id="issueType" required>
                  <option value="maintenance">Maintenance</option>
                  <option value="damage">Damage</option>
                  <option value="loss">Loss</option>
                  <option value="theft">Theft</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Priority <span class="text-danger">*</span></label>
                <select class="form-select" id="issuePriority" required>
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Assign To</label>
              <select class="form-select" id="issueAssignedTo">
                <option value="">Not Assigned</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea class="form-control" rows="4" id="issueDescription" required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="btnSaveIssue">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Issue Detail Modal -->
  <div class="modal fade" id="issueDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">Issue Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="issueDetailContent">
          <div class="text-center py-4"><div class="spinner-border"></div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bulk Actions Modal -->
  <div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Bulk Actions</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="bulkActionsForm">
            <div class="mb-3">
              <label class="form-label">Action <span class="text-danger">*</span></label>
              <select class="form-select" id="bulkAction" required>
                <option value="">Select Action</option>
                <option value="assign">Assign To</option>
                <option value="update_status">Update Status</option>
                <option value="update_priority">Update Priority</option>
                <option value="delete">Delete</option>
              </select>
            </div>
            <div class="mb-3" id="bulkAssignToGroup" style="display:none;">
              <label class="form-label">Assign To</label>
              <select class="form-select" id="bulkAssignTo"></select>
            </div>
            <div class="mb-3" id="bulkStatusGroup" style="display:none;">
              <label class="form-label">Status</label>
              <select class="form-select" id="bulkStatus">
                <option value="reported">Reported</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="mb-3" id="bulkPriorityGroup" style="display:none;">
              <label class="form-label">Priority</label>
              <select class="form-select" id="bulkPriority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="alert alert-info">
              <strong id="bulkSelectedCount">0</strong> issue(s) selected
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success" id="btnExecuteBulkAction">Execute</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const token = '{{ csrf_token() }}';
  const catModal = new bootstrap.Modal(document.getElementById('catModal'));
  const assetModal = new bootstrap.Modal(document.getElementById('assetModal'));

  // Load Categories
  async function loadCategories(){
    const tbody = document.querySelector('#tblCategories tbody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>';
    try {
      const res = await fetch('{{ route('assets.categories.list') }}');
      if(!res.ok){
        console.error('Categories list failed with status', res.status);
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Failed to load: ' + res.status + '</td></tr>';
        return;
      }
      const data = await res.json();
      if(!data.success){
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Failed to load: ' + (data.message || 'Unknown error') + '</td></tr>';
        return;
      }
      const items = Array.isArray(data.items) ? data.items : [];
      if(items.length === 0){
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No categories found</td></tr>';
        return;
      }
      tbody.innerHTML = items.map(c=>`
        <tr data-id="${c.id}" data-json='${JSON.stringify(c)}'>
          <td>${c.code}</td>
          <td>${c.name}</td>
          <td>${c.depreciation_years}y @ ${Number(c.depreciation_rate).toFixed(2)}%</td>
          <td><span class="badge bg-${c.is_active?'success':'secondary'}">${c.is_active?'Active':'Inactive'}</span></td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-info btnCatEdit">Edit</button>
            <button class="btn btn-sm btn-outline-danger btnCatDelete">Delete</button>
          </td>
        </tr>`).join('');
      document.querySelectorAll('.btnCatEdit').forEach(b=>b.onclick=()=>openCatModal(JSON.parse(b.closest('tr').dataset.json)));
      document.querySelectorAll('.btnCatDelete').forEach(b=>b.onclick=()=>deleteCategory(b.closest('tr').dataset.id));
    } catch (error) {
      console.error('Error loading categories:', error);
      tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Error: ' + (error.message || 'Failed to load') + '</td></tr>';
    }
  }

  // Load Assets
  async function loadAssets(){
    const tbody = document.querySelector('#tblAssets tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>';
    try {
      const res = await fetch('{{ route('assets.items.list') }}');
      if(!res.ok){
        tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Failed to load: ' + res.status + '</td></tr>';
        return;
      }
      const data = await res.json();
      if(!data.success){ 
        tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Failed to load: ' + (data.message || 'Unknown error') + '</td></tr>'; 
        return; 
      }
      const items = data.items && data.items.data ? data.items.data : (data.items || []);
      if(items.length === 0){
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No assets found</td></tr>';
        return;
      }
      tbody.innerHTML = items.map(a=>`
        <tr data-id="${a.id}" data-json='${JSON.stringify(a)}'>
          <td>${a.asset_tag || ''}</td>
          <td>${a.name || ''}</td>
          <td>${a.category ? a.category.name : ''}</td>
          <td><span class="badge bg-${badgeByStatus(a.status || 'available')}">${a.status || 'available'}</span></td>
          <td>${a.assigned_user ? a.assigned_user.name : (a.assignedUser ? a.assignedUser.name : '')}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-info btnAssetEdit">Edit</button>
            <button class="btn btn-sm btn-outline-danger btnAssetDelete">Delete</button>
          </td>
        </tr>`).join('');
      document.querySelectorAll('.btnAssetEdit').forEach(b=>b.onclick=()=>openAssetModal(JSON.parse(b.closest('tr').dataset.json)));
      document.querySelectorAll('.btnAssetDelete').forEach(b=>b.onclick=()=>deleteAsset(b.closest('tr').dataset.id));
    } catch(error){
      console.error('Error loading assets:', error);
      tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Error: ' + error.message + '</td></tr>';
    }
  }

  function badgeByStatus(s){ return s==='available'?'success':(s==='assigned'?'primary':(s==='maintenance'?'warning':(s==='disposed'?'dark':'secondary'))); }

  // Modals
  document.getElementById('btnNewCat').onclick=()=>openCatModal();
  function openCatModal(cat){
    document.getElementById('catId').value = cat?.id || '';
    document.getElementById('catCode').value = cat?.code || '';
    document.getElementById('catName').value = cat?.name || '';
    document.getElementById('catDesc').value = cat?.description || '';
    document.getElementById('catYears').value = cat?.depreciation_years || 5;
    document.getElementById('catRate').value = cat?.depreciation_rate || 20;
    document.getElementById('catActive').checked = cat?.is_active !== false;
    document.getElementById('catModalTitle').innerText = cat ? 'Edit Category' : 'New Category';
    catModal.show();
  }
  document.getElementById('btnSaveCat').onclick=()=>{
    const id = document.getElementById('catId').value;
    const payload = {
      code: document.getElementById('catCode').value.trim(),
      name: document.getElementById('catName').value.trim(),
      description: document.getElementById('catDesc').value.trim(),
      depreciation_years: document.getElementById('catYears').value,
      depreciation_rate: document.getElementById('catRate').value,
      is_active: document.getElementById('catActive').checked?1:0
    };
    const url = id ? `{{ route('assets.categories.update', ':id') }}`.replace(':id', id) : `{{ route('assets.categories.store') }}`;
    postForm(url, payload, id ? 'PUT' : 'POST').then((result)=>{
      if(result.success) {
        catModal.hide(); 
        loadCategories(); 
      } else {
        alert(result.message || 'Failed to save category');
      }
    });
  };

  // Asset modal
  document.getElementById('btnNewAsset').onclick=()=>openAssetModal();
  async function openAssetModal(a){
    // populate categories select
    const catRes = await fetch('{{ route('assets.categories.list') }}');
    const catData = await catRes.json();
    const sel = document.getElementById('assetCategory');
    sel.innerHTML = catData.items.map(c=>`<option value="${c.id}">${c.name}</option>`).join('');
    document.getElementById('assetId').value = a?.id || '';
    document.getElementById('assetTag').value = a?.asset_tag || '';
    document.getElementById('assetName').value = a?.name || '';
    document.getElementById('assetCategory').value = a?.category_id || (catData.items[0]?.id||'');
    document.getElementById('assetStatus').value = a?.status || 'available';
    document.getElementById('assetCondition').value = a?.condition || 'good';
    document.getElementById('assetPurchaseDate').value = a?.purchase_date || '';
    document.getElementById('assetPrice').value = a?.purchase_price || '';
    document.getElementById('assetWarranty').value = a?.warranty_expiry || '';
    document.getElementById('assetDesc').value = a?.description || '';
    document.getElementById('assetModalTitle').innerText = a ? 'Edit Asset' : 'New Asset';
    assetModal.show();
  }
  document.getElementById('btnSaveAsset').onclick=()=>{
    const id = document.getElementById('assetId').value;
    const payload = {
      asset_tag: document.getElementById('assetTag').value.trim(),
      name: document.getElementById('assetName').value.trim(),
      category_id: document.getElementById('assetCategory').value,
      status: document.getElementById('assetStatus').value,
      condition: document.getElementById('assetCondition').value,
      purchase_date: document.getElementById('assetPurchaseDate').value,
      purchase_price: document.getElementById('assetPrice').value,
      warranty_expiry: document.getElementById('assetWarranty').value,
      description: document.getElementById('assetDesc').value.trim()
    };
    const url = id ? `{{ route('assets.items.update', ':id') }}`.replace(':id', id) : `{{ route('assets.items.store') }}`;
    postForm(url, payload).then(()=>{ assetModal.hide(); loadAssets(); });
  };

  function postForm(url, data, method='POST'){ 
    const fd=new FormData(); 
    Object.entries(data).forEach(([k,v])=>fd.append(k,v)); 
    if(method !== 'POST') fd.append('_method', method);
    return fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'},body:fd})
      .then(async r=>{
        const json = await r.json();
        if(!r.ok) throw new Error(json.message || 'Request failed');
        return json;
      })
      .catch(e=>{console.error('Error:',e); return {success:false,message:e.message};}); 
  }

  async function deleteCategory(id){
    if(!confirm('Delete this category?')) return;
    try {
      const res = await fetch(`{{ route('assets.categories.destroy', ':id') }}`.replace(':id', id), {method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});
      const data = await res.json();
      if(data.success) loadCategories(); else alert(data.message || 'Failed to delete');
    } catch(e) { alert('Error: ' + e.message); }
  }

  async function deleteAsset(id){
    if(!confirm('Delete this asset?')) return;
    try {
      const res = await fetch(`{{ route('assets.items.destroy', ':id') }}`.replace(':id', id), {method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});
      const data = await res.json();
      if(data.success) loadAssets(); else alert(data.message || 'Failed to delete');
    } catch(e) { alert('Error: ' + e.message); }
  }

  // ========== ISSUE MANAGEMENT ==========
  let selectedIssues = new Set();
  let currentIssuePage = 1;
  let issueFilters = {};

  // Load Statistics
  async function loadIssueStatistics() {
    try {
      const res = await fetch('{{ route("assets.issues.statistics") }}');
      const data = await res.json();
      if (data.success) {
        document.getElementById('statTotal').textContent = data.stats.total || 0;
        document.getElementById('statReported').textContent = data.stats.reported || 0;
        document.getElementById('statInProgress').textContent = data.stats.in_progress || 0;
        document.getElementById('statResolved').textContent = data.stats.resolved || 0;
      }
    } catch (e) {
      console.error('Error loading statistics:', e);
    }
  }

  // Load Issues
  async function loadIssues(page = 1) {
    currentIssuePage = page;
    const tbody = document.querySelector('#tblIssues tbody');
    tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted">Loading...</td></tr>';
    
    try {
      const params = new URLSearchParams({
        page: page,
        ...issueFilters
      });
      
      const res = await fetch(`{{ route('assets.issues.list') }}?${params}`);
      const data = await res.json();
      
      if (!data.success) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-danger">${data.message || 'Failed to load'}</td></tr>`;
        return;
      }
      
      const items = data.items?.data || data.items || [];
      document.getElementById('issuesCount').textContent = `${items.length} issue(s) found`;
      
      if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted">No issues found</td></tr>';
        return;
      }
      
      tbody.innerHTML = items.map(issue => {
        const priorityBadge = {
          low: 'bg-secondary',
          medium: 'bg-info',
          high: 'bg-warning',
          urgent: 'bg-danger'
        }[issue.priority] || 'bg-secondary';
        
        const statusBadge = {
          reported: 'bg-warning',
          in_progress: 'bg-info',
          resolved: 'bg-success',
          closed: 'bg-dark',
          cancelled: 'bg-secondary'
        }[issue.status] || 'bg-secondary';
        
        return `
          <tr data-id="${issue.id}">
            <td><input type="checkbox" class="issue-checkbox" value="${issue.id}"></td>
            <td>#${issue.id}</td>
            <td>${issue.asset?.asset_tag || ''} - ${issue.asset?.name || ''}</td>
            <td><strong>${issue.title}</strong></td>
            <td><span class="badge bg-primary">${issue.issue_type}</span></td>
            <td><span class="badge ${priorityBadge}">${issue.priority}</span></td>
            <td><span class="badge ${statusBadge}">${issue.status.replace('_', ' ')}</span></td>
            <td>${issue.reported_by?.name || issue.reportedBy?.name || 'N/A'}</td>
            <td>${issue.assigned_to?.name || issue.assignedTo?.name || 'Unassigned'}</td>
            <td>${issue.reported_date ? new Date(issue.reported_date).toLocaleDateString() : 'N/A'}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-info btnViewIssue" data-id="${issue.id}">View</button>
              <button class="btn btn-sm btn-outline-primary btnEditIssue" data-id="${issue.id}">Edit</button>
            </td>
          </tr>
        `;
      }).join('');
      
      // Attach event listeners
      document.querySelectorAll('.issue-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedIssues);
      });
      
      document.querySelectorAll('.btnViewIssue').forEach(btn => {
        btn.addEventListener('click', () => viewIssueDetail(btn.dataset.id));
      });
      
      document.querySelectorAll('.btnEditIssue').forEach(btn => {
        btn.addEventListener('click', () => editIssue(btn.dataset.id));
      });
      
      // Update pagination if needed
      if (data.items?.total_pages > 1) {
        updateIssuePagination(data.items);
      }
      
    } catch (e) {
      console.error('Error loading issues:', e);
      tbody.innerHTML = '<tr><td colspan="11" class="text-danger">Error loading issues</td></tr>';
    }
  }

  function updateSelectedIssues() {
    selectedIssues.clear();
    document.querySelectorAll('.issue-checkbox:checked').forEach(cb => {
      selectedIssues.add(cb.value);
    });
    
    const btnBulk = document.getElementById('btnBulkActions');
    btnBulk.disabled = selectedIssues.size === 0;
  }

  document.getElementById('selectAllIssues').addEventListener('change', function() {
    document.querySelectorAll('.issue-checkbox').forEach(cb => {
      cb.checked = this.checked;
      if (this.checked) selectedIssues.add(cb.value);
      else selectedIssues.delete(cb.value);
    });
    document.getElementById('btnBulkActions').disabled = selectedIssues.size === 0;
  });

  // Filters
  document.getElementById('btnApplyFilters').addEventListener('click', function() {
    issueFilters = {
      status: document.getElementById('filterStatus').value,
      priority: document.getElementById('filterPriority').value,
      issue_type: document.getElementById('filterType').value,
      date_from: document.getElementById('filterDateFrom').value,
      date_to: document.getElementById('filterDateTo').value
    };
    Object.keys(issueFilters).forEach(k => {
      if (!issueFilters[k]) delete issueFilters[k];
    });
    loadIssues(1);
  });

  // Search
  let searchTimeout;
  document.getElementById('searchIssues').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      if (this.value.trim()) {
        issueFilters.search = this.value.trim();
      } else {
        delete issueFilters.search;
      }
      loadIssues(1);
    }, 500);
  });

  // Report Issue
  document.getElementById('btnReportIssue').addEventListener('click', async function() {
    const issueModal = new bootstrap.Modal(document.getElementById('issueModal'));
    document.getElementById('issueModalTitle').textContent = 'Report New Issue';
    document.getElementById('issueId').value = '';
    document.getElementById('issueForm').reset();
    
    // Load assets
    const assetRes = await fetch('{{ route("assets.items.list") }}');
    const assetData = await assetRes.json();
    const assetSelect = document.getElementById('issueAsset');
    assetSelect.innerHTML = '<option value="">Select Asset</option>';
    if (assetData.success && assetData.items?.data) {
      assetData.items.data.forEach(a => {
        assetSelect.innerHTML += `<option value="${a.id}">${a.asset_tag} - ${a.name}</option>`;
      });
    }
    
    // Load users
    const userSelect = document.getElementById('issueAssignedTo');
    userSelect.innerHTML = '<option value="">Not Assigned</option>';
    @if(isset($users))
      @foreach($users as $user)
        userSelect.innerHTML += `<option value="{{ $user->id }}">{{ $user->name }}</option>`;
      @endforeach
    @endif
    
    issueModal.show();
  });

  // Save Issue
  document.getElementById('btnSaveIssue').addEventListener('click', async function() {
    const issueId = document.getElementById('issueId').value;
    let assetId = document.getElementById('issueAsset').value || document.getElementById('issueAssetId').value;
    
    if (!issueId && !assetId) {
      alert('Please select an asset');
      return;
    }
    
    const payload = {
      title: document.getElementById('issueTitle').value,
      issue_type: document.getElementById('issueType').value,
      priority: document.getElementById('issuePriority').value,
      description: document.getElementById('issueDescription').value,
      assigned_to: document.getElementById('issueAssignedTo').value || null
    };
    
    let url;
    if (issueId) {
      url = `{{ route('assets.issues.update', ':id') }}`.replace(':id', issueId);
    } else {
      url = `{{ route('assets.issues.store', ':id') }}`.replace(':id', assetId);
    }
    
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('issueModal')).hide();
        loadIssues();
        loadIssueStatistics();
      } else {
        alert(data.message || 'Failed to save issue');
      }
    } catch (e) {
      alert('Error: ' + e.message);
    }
  });

  // View Issue Detail
  async function viewIssueDetail(issueId) {
    const modal = new bootstrap.Modal(document.getElementById('issueDetailModal'));
    const content = document.getElementById('issueDetailContent');
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div></div>';
    modal.show();
    
    try {
      const res = await fetch(`{{ route('assets.issues.show', ':id') }}`.replace(':id', issueId));
      const data = await res.json();
      const historyRes = await fetch(`{{ route('assets.issues.history', ':id') }}`.replace(':id', issueId));
      const historyData = await historyRes.json();
      
      if (data.success) {
        const issue = data.item;
        
        // Build assigned to dropdown options
        let assignedToOptions = '<option value="">Not Assigned</option>';
        @if(isset($users))
          @foreach($users as $user)
            const assignedToId = issue.assigned_to?.id || issue.assignedTo?.id;
            const isSelected = assignedToId == {{ $user->id }} ? 'selected' : '';
            assignedToOptions += `<option value="{{ $user->id }}" ${isSelected}>{{ $user->name }}</option>`;
          @endforeach
        @endif
        
        content.innerHTML = `
          <div class="row">
            <div class="col-md-8">
              <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Issue Information</h6></div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tr><th>Title:</th><td>${issue.title}</td></tr>
                    <tr><th>Asset:</th><td>${issue.asset?.asset_tag} - ${issue.asset?.name}</td></tr>
                    <tr><th>Type:</th><td><span class="badge bg-primary">${issue.issue_type}</span></td></tr>
                    <tr><th>Priority:</th><td><span class="badge bg-${issue.priority === 'urgent' ? 'danger' : issue.priority === 'high' ? 'warning' : 'info'}">${issue.priority}</span></td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-${issue.status === 'resolved' ? 'success' : issue.status === 'in_progress' ? 'info' : 'warning'}">${issue.status}</span></td></tr>
                    <tr><th>Reported By:</th><td>${issue.reported_by?.name || issue.reportedBy?.name || 'N/A'}</td></tr>
                    <tr><th>Assigned To:</th><td>${issue.assigned_to?.name || issue.assignedTo?.name || 'Unassigned'}</td></tr>
                    <tr><th>Reported Date:</th><td>${issue.reported_date ? new Date(issue.reported_date).toLocaleString() : 'N/A'}</td></tr>
                    <tr><th>Resolved Date:</th><td>${issue.resolved_date ? new Date(issue.resolved_date).toLocaleString() : 'Pending'}</td></tr>
                    <tr><th>Cost:</th><td>${issue.cost ? 'TZS ' + parseFloat(issue.cost).toLocaleString() : 'N/A'}</td></tr>
                  </table>
                  <div class="mt-3">
                    <strong>Description:</strong>
                    <p class="mt-2">${issue.description || 'N/A'}</p>
                  </div>
                  ${issue.resolution_notes ? `
                    <div class="mt-3">
                      <strong>Resolution Notes:</strong>
                      <p class="mt-2">${issue.resolution_notes}</p>
                    </div>
                  ` : ''}
                  <div class="mt-4">
                    <h6>Update Issue</h6>
                    <form id="updateIssueForm">
                      <div class="row g-2">
                        <div class="col-md-4">
                          <label class="form-label small">Status</label>
                          <select class="form-select form-select-sm" id="updateIssueStatus">
                            <option value="reported" ${issue.status === 'reported' ? 'selected' : ''}>Reported</option>
                            <option value="in_progress" ${issue.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="resolved" ${issue.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                            <option value="closed" ${issue.status === 'closed' ? 'selected' : ''}>Closed</option>
                            <option value="cancelled" ${issue.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Assign To</label>
                          <select class="form-select form-select-sm" id="updateIssueAssignedTo">
                            ${assignedToOptions}
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Cost (TZS)</label>
                          <input type="number" step="0.01" class="form-control form-control-sm" id="updateIssueCost" value="${issue.cost || ''}">
                        </div>
                        <div class="col-12">
                          <label class="form-label small">Resolution Notes</label>
                          <textarea class="form-control form-control-sm" rows="3" id="updateIssueNotes">${issue.resolution_notes || ''}</textarea>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-sm btn-primary" onclick="updateIssueFromDetail(${issue.id})">
                            <i class="bx bx-save"></i> Update Issue
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-header"><h6 class="mb-0">Timeline</h6></div>
                <div class="card-body">
                  ${historyData.success && historyData.history ? historyData.history.map(h => `
                    <div class="mb-3 pb-3 border-bottom">
                      <div class="small text-muted">${new Date(h.date).toLocaleString()}</div>
                      <div class="fw-bold">${h.action}</div>
                      <div class="small">${h.user}</div>
                      <div class="small text-muted">${h.notes}</div>
                    </div>
                  `).join('') : '<p class="text-muted">No history available</p>'}
                </div>
              </div>
            </div>
          </div>
        `;
      }
    } catch (e) {
      content.innerHTML = '<div class="alert alert-danger">Error loading issue details</div>';
    }
  }

  // Bulk Actions
  document.getElementById('btnBulkActions').addEventListener('click', function() {
    if (selectedIssues.size === 0) return;
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    document.getElementById('bulkSelectedCount').textContent = selectedIssues.size;
    document.getElementById('bulkActionsForm').reset();
    document.getElementById('bulkAssignToGroup').style.display = 'none';
    document.getElementById('bulkStatusGroup').style.display = 'none';
    document.getElementById('bulkPriorityGroup').style.display = 'none';
    
    // Load users for assign dropdown
    const assignSelect = document.getElementById('bulkAssignTo');
    assignSelect.innerHTML = '<option value="">Select User</option>';
    @if(isset($users))
      @foreach($users as $user)
        assignSelect.innerHTML += `<option value="{{ $user->id }}">{{ $user->name }}</option>`;
      @endforeach
    @endif
    
    document.getElementById('bulkAction').addEventListener('change', function() {
      document.getElementById('bulkAssignToGroup').style.display = this.value === 'assign' ? 'block' : 'none';
      document.getElementById('bulkStatusGroup').style.display = this.value === 'update_status' ? 'block' : 'none';
      document.getElementById('bulkPriorityGroup').style.display = this.value === 'update_priority' ? 'block' : 'none';
    });
    
    modal.show();
  });

  document.getElementById('btnExecuteBulkAction').addEventListener('click', async function() {
    const action = document.getElementById('bulkAction').value;
    const payload = {
      issue_ids: Array.from(selectedIssues),
      action: action
    };
    
    if (action === 'assign') {
      payload.assigned_to = document.getElementById('bulkAssignTo').value;
    } else if (action === 'update_status') {
      payload.status = document.getElementById('bulkStatus').value;
    } else if (action === 'update_priority') {
      payload.priority = document.getElementById('bulkPriority').value;
    }
    
    try {
      const res = await fetch('{{ route("assets.issues.bulk-update") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
        selectedIssues.clear();
        loadIssues();
        loadIssueStatistics();
      } else {
        alert(data.message || 'Failed to execute bulk action');
      }
    } catch (e) {
      alert('Error: ' + e.message);
    }
  });

  // Export Issues
  document.getElementById('btnExportIssues').addEventListener('click', function() {
    const params = new URLSearchParams(issueFilters);
    window.location.href = `{{ route('assets.issues.export') }}?${params}`;
  });

  // Update Issue from Detail Modal
  async function updateIssueFromDetail(issueId) {
    const payload = {
      status: document.getElementById('updateIssueStatus').value,
      assigned_to: document.getElementById('updateIssueAssignedTo').value || null,
      resolution_notes: document.getElementById('updateIssueNotes').value,
      cost: document.getElementById('updateIssueCost').value || null
    };
    
    try {
      const res = await fetch(`{{ route('assets.issues.update', ':id') }}`.replace(':id', issueId), {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        alert('Issue updated successfully!');
        viewIssueDetail(issueId); // Reload
        loadIssues();
        loadIssueStatistics();
      } else {
        alert(data.message || 'Failed to update issue');
      }
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }

  // Edit Issue
  async function editIssue(issueId) {
    const res = await fetch(`{{ route('assets.issues.show', ':id') }}`.replace(':id', issueId));
    const data = await res.json();
    if (data.success) {
      const issue = data.item;
      const modal = new bootstrap.Modal(document.getElementById('issueModal'));
      document.getElementById('issueModalTitle').textContent = 'Edit Issue';
      document.getElementById('issueId').value = issue.id;
      document.getElementById('issueAssetId').value = issue.asset_id;
      document.getElementById('issueTitle').value = issue.title;
      document.getElementById('issueType').value = issue.issue_type;
      document.getElementById('issuePriority').value = issue.priority;
      document.getElementById('issueDescription').value = issue.description;
      
      // Load assets
      const assetRes = await fetch('{{ route("assets.items.list") }}');
      const assetData = await assetRes.json();
      const assetSelect = document.getElementById('issueAsset');
      assetSelect.innerHTML = '<option value="">Select Asset</option>';
      if (assetData.success && assetData.items?.data) {
        assetData.items.data.forEach(a => {
          const selected = a.id == issue.asset_id ? 'selected' : '';
          assetSelect.innerHTML += `<option value="${a.id}" ${selected}>${a.asset_tag} - ${a.name}</option>`;
        });
      }
      
      // Set assigned to
      const assignedTo = issue.assigned_to?.id || issue.assignedTo?.id || '';
      document.getElementById('issueAssignedTo').value = assignedTo;
      
      modal.show();
    }
  }

  // Load issues when tab is shown
  document.querySelector('a[href="#tabIssues"]').addEventListener('shown.bs.tab', function() {
    loadIssueStatistics();
    loadIssues();
  });

  // initial loads
  loadCategories();
  loadAssets();
})();
</script>
@endpush
