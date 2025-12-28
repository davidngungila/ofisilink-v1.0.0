@extends('layouts.app')

@section('title', 'Finance Settings')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Finance Settings</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
        height: 100%;
    }

    .summary-card[data-type="gl"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="cashbox"] {
        border-left-color: #28a745 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.15s ease;
    }

    .badge-category {
        font-size: 0.75rem;
        padding: 4px 10px;
        font-weight: 600;
    }

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

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .search-box input {
        padding-left: 38px;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section with Gradient Background -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-cog me-2"></i>Finance Settings
                    </h2>
                    <p class="mb-0 opacity-90">Configure GL Accounts and Cash Boxes for financial transactions</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button class="btn btn-warning btn-sm" id="btnSyncAll" title="Sync All to Chart of Accounts">
                        <i class="bx bx-sync"></i> Sync All
                    </button>
                    <button class="btn btn-light btn-sm" id="btnRefresh" title="Refresh">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="gl">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total GL Accounts</h6>
                            <h3 class="mb-0 text-primary fw-bold">{{ $gls->count() }}</h3>
                            <small class="text-muted">{{ $gls->where('is_active', true)->count() }} active</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-book-open fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="cashbox">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Cash Boxes</h6>
                            <h3 class="mb-0 text-success fw-bold">{{ $cashBoxes->count() }}</h3>
                            <small class="text-muted">{{ $cashBoxes->where('is_active', true)->count() }} active</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-wallet fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GL Accounts and Cash Boxes -->
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bx bx-book-open me-2"></i>GL Accounts
                        </h6>
                        <span class="badge bg-primary">{{ $gls->count() }}</span>
                    </div>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <div class="search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" id="glSearch" class="form-control form-control-sm" placeholder="Search..." style="max-width:200px">
                        </div>
                        <button class="btn btn-sm btn-primary" id="btnAddGl">
                            <i class="bx bx-plus"></i> New Account
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="glTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:15%">Code</th>
                                    <th>Name</th>
                                    <th style="width:18%">Category</th>
                                    <th style="width:15%">Chart of Account</th>
                                    <th style="width:10%">Status</th>
                                    <th style="width:18%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="glBody">
                                @forelse($gls as $gl)
                                <tr data-id="{{ $gl->id }}" class="fade-in">
                                    <td><code class="text-primary fw-bold">{{ $gl->code }}</code></td>
                                    <td class="fw-medium">{{ $gl->name }}</td>
                                    <td>
                                        @if($gl->category)
                                        <span class="badge badge-category bg-secondary">{{ $gl->category }}</span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gl->chartOfAccount)
                                        <a href="{{ route('modules.accounting.chart-of-accounts') }}?search={{ $gl->chartOfAccount->code }}" 
                                           class="text-decoration-none" title="View in Chart of Accounts">
                                            <span class="badge bg-success">
                                                <i class="bx bx-link-external"></i> {{ $gl->chartOfAccount->code }}
                                            </span>
                                        </a>
                                        @else
                                        <span class="badge bg-warning" title="Not synced to Chart of Accounts">
                                            <i class="bx bx-x-circle"></i> Not Synced
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $gl->is_active ? 'success':'secondary' }}">
                                            {{ $gl->is_active ? 'Active':'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-info btnGlEdit" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btnGlDelete" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="bx bx-book-open"></i>
                                        <p class="mb-0">No GL accounts found. Click "New Account" to create one.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bx bx-wallet me-2"></i>Cash Boxes
                        </h6>
                        <span class="badge bg-success">{{ $cashBoxes->count() }}</span>
                    </div>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <div class="search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" id="cbSearch" class="form-control form-control-sm" placeholder="Search..." style="max-width:200px">
                        </div>
                        <button class="btn btn-sm btn-primary" id="btnAddCb">
                            <i class="bx bx-plus"></i> New Cash Box
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="cbTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th style="width:12%">Currency</th>
                                    <th style="width:15%" class="text-end">Balance</th>
                                    <th style="width:18%">Chart of Account</th>
                                    <th style="width:10%">Status</th>
                                    <th style="width:18%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="cbBody">
                                @forelse($cashBoxes as $cb)
                                <tr data-id="{{ $cb->id }}" class="fade-in">
                                    <td class="fw-bold">{{ $cb->name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $cb->currency ?? 'TZS' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold">{{ number_format($cb->current_balance ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($cb->chartOfAccount)
                                        <a href="{{ route('modules.accounting.chart-of-accounts') }}?search={{ $cb->chartOfAccount->code }}" 
                                           class="text-decoration-none" title="View in Chart of Accounts">
                                            <span class="badge bg-success">
                                                <i class="bx bx-link-external"></i> {{ $cb->chartOfAccount->code }}
                                            </span>
                                        </a>
                                        @else
                                        <span class="badge bg-warning" title="Not synced to Chart of Accounts">
                                            <i class="bx bx-x-circle"></i> Not Synced
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $cb->is_active ? 'success':'secondary' }}">
                                            {{ $cb->is_active ? 'Active':'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-info btnCbEdit" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btnCbDelete" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="bx bx-wallet"></i>
                                        <p class="mb-0">No cash boxes found. Click "New Cash Box" to create one.</p>
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

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const token='{{ csrf_token() }}';
  const glSearch = document.getElementById('glSearch');
  const cbSearch = document.getElementById('cbSearch');
  const btnRefresh = document.getElementById('btnRefresh');
  const btnSyncAll = document.getElementById('btnSyncAll');
  
  if(btnRefresh){ 
    btnRefresh.addEventListener('click', ()=>{
      showLoading(true);
      setTimeout(() => {
        location.reload();
      }, 300);
    }); 
  }

  if(btnSyncAll){
    btnSyncAll.addEventListener('click', async ()=>{
      if(!confirm('This will sync all GL Accounts and Cash Boxes to Chart of Accounts. Continue?')){
        return;
      }
      showLoading(true);
      try {
        const response = await fetch('{{ route('finance.settings.sync-all') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          }
        });
        const result = await response.json();
        if(result.success){
          if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.success('Success', result.message || 'All accounts synced successfully', { duration: 5000 });
          } else {
            alert(result.message || 'All accounts synced successfully');
          }
          setTimeout(() => location.reload(), 1000);
        } else {
          throw new Error(result.message || 'Sync failed');
        }
      } catch(error){
        console.error('Sync error:', error);
        if (typeof window.AdvancedToast !== 'undefined') {
          window.AdvancedToast.error('Error', error.message || 'Failed to sync accounts', { duration: 5000 });
        } else {
          alert('Error: ' + (error.message || 'Failed to sync accounts'));
        }
        showLoading(false);
      }
    });
  }

  function showLoading(show = true){
    const overlay = document.getElementById('loadingOverlay');
    if(overlay) overlay.style.display = show ? 'flex' : 'none';
  }

  function filterTable(input, tableId){
    const q=(input.value||'').toLowerCase();
    const rows=document.querySelectorAll(`#${tableId} tbody tr`);
    let visibleCount = 0;
    rows.forEach(r=>{
      const text=r.innerText.toLowerCase();
      const isVisible = text.includes(q);
      r.style.display = isVisible ? '' : 'none';
      if(isVisible) visibleCount++;
    });
    
    // Show empty state if no results
    const tbody = document.querySelector(`#${tableId} tbody`);
    let emptyRow = tbody.querySelector('.empty-state-row');
    if(visibleCount === 0 && !emptyRow && q !== ''){
      emptyRow = document.createElement('tr');
      emptyRow.className = 'empty-state-row';
      emptyRow.innerHTML = `
        <td colspan="5" class="empty-state">
          <i class="bx bx-search"></i>
          <p class="mb-0">No results found for "${escapeHtml(q)}"</p>
        </td>
      `;
      tbody.appendChild(emptyRow);
    } else if(emptyRow && (visibleCount > 0 || q === '')){
      emptyRow.remove();
    }
  }
  
  if(glSearch){ 
    glSearch.addEventListener('input', ()=>filterTable(glSearch,'glTable')); 
  }
  if(cbSearch){ 
    cbSearch.addEventListener('input', ()=>filterTable(cbSearch,'cbTable')); 
  }
  
  // Open GL modal
  document.getElementById('btnAddGl').addEventListener('click', ()=>openGlModal());
  document.querySelectorAll('.btnGlEdit').forEach(b=>b.addEventListener('click', function(){
    const tr=this.closest('tr');
    const code = tr.querySelector('code')?.innerText || '';
    const name = tr.children[1].innerText.trim();
    const category = tr.children[2].querySelector('.badge')?.innerText || '';
    const isActive = tr.children[3].querySelector('.badge')?.innerText.includes('Active');
    openGlModal({id:tr.dataset.id, code, name, category, is_active: isActive});
  }));
  document.querySelectorAll('.btnGlDelete').forEach(b=>b.addEventListener('click', function(){ 
    if(confirm('Are you sure you want to delete this GL Account? This action cannot be undone.')) {
      showLoading(true);
      submitGl({_method:'DELETE'}, this.closest('tr').dataset.id); 
    }
  }));

  // Open CashBox modal
  document.getElementById('btnAddCb').addEventListener('click', ()=>openCbModal());
  document.querySelectorAll('.btnCbEdit').forEach(b=>b.addEventListener('click', function(){
    const tr=this.closest('tr');
    const name = tr.children[0].innerText.trim();
    const currency = tr.children[1].querySelector('.badge')?.innerText || 'TZS';
    const balance = tr.children[2].innerText.replace(/[^0-9.\-]/g,'');
    const isActive = tr.children[3].querySelector('.badge')?.innerText.includes('Active');
    openCbModal({id:tr.dataset.id, name, currency, current_balance: balance, is_active: isActive});
  }));
  document.querySelectorAll('.btnCbDelete').forEach(b=>b.addEventListener('click', function(){ 
    if(confirm('Are you sure you want to delete this Cash Box? This action cannot be undone.')) {
      showLoading(true);
      submitCb({_method:'DELETE'}, this.closest('tr').dataset.id); 
    }
  }));

  function openGlModal(initial){
    const formHtml = `
      <form id="glForm">
        <div class="mb-3">
          <label class="form-label fw-semibold">Account Code <span class="text-danger">*</span></label>
          <input class="form-control" id="gl_code" value="${escapeHtml(initial?.code||'')}" required 
                 placeholder="e.g., GL-001" maxlength="50">
          <small class="text-muted">Unique identifier for the account</small>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
          <input class="form-control" id="gl_name" value="${escapeHtml(initial?.name||'')}" required 
                 placeholder="e.g., Office Supplies" maxlength="255">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Category</label>
          <select class="form-select" id="gl_category">
            <option value="">Select Category</option>
            ${['Assets','Liabilities','Equity','Income','Expense'].map(c=>
              `<option value="${c}" ${initial?.category===c?'selected':''}>${c}</option>`
            ).join('')}
          </select>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gl_active" ${initial?.is_active? 'checked':''}>
          <label class="form-check-label" for="gl_active">
            Active Account
          </label>
        </div>
      </form>
    `;
    modalPrompt(initial? 'Edit GL Account':'Create New GL Account', formHtml, async ()=>{
      const form = document.getElementById('glForm');
      if(!form.checkValidity()){
        form.reportValidity();
        return;
      }
      const payload={ 
        code:val('gl_code').trim(), 
        name:val('gl_name').trim(), 
        category:val('gl_category'), 
        is_active:checked('gl_active')?1:0 
      };
      showLoading(true);
      await submitGl(payload, initial?.id);
    });
  }
  
  function openCbModal(initial){
    const formHtml = `
      <form id="cbForm">
        <div class="mb-3">
          <label class="form-label fw-semibold">Cash Box Name <span class="text-danger">*</span></label>
          <input class="form-control" id="cb_name" value="${escapeHtml(initial?.name||'')}" required 
                 placeholder="e.g., Main Cash Box" maxlength="100">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Currency</label>
          <select class="form-select" id="cb_currency">
            ${['TZS','USD','EUR','KES','GBP'].map(c=>
              `<option value="${c}" ${initial?.currency===c?'selected':''}>${c}</option>`
            ).join('')}
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Opening Balance</label>
          <input type="number" step="0.01" class="form-control" id="cb_balance" 
                 value="${escapeHtml(initial?.current_balance||'0')}" placeholder="0.00">
          <small class="text-muted">Initial balance for this cash box</small>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="cb_active" ${initial?.is_active? 'checked':''}>
          <label class="form-check-label" for="cb_active">
            Active Cash Box
          </label>
        </div>
      </form>
    `;
    modalPrompt(initial? 'Edit Cash Box':'Create New Cash Box', formHtml, async ()=>{
      const form = document.getElementById('cbForm');
      if(!form.checkValidity()){
        form.reportValidity();
        return;
      }
      const payload={ 
        name:val('cb_name').trim(), 
        currency:val('cb_currency'), 
        current_balance:val('cb_balance') || 0, 
        is_active:checked('cb_active')?1:0 
      };
      showLoading(true);
      await submitCb(payload, initial?.id);
    });
  }
  
  async function submitGl(data, id){
    try {
      const url = id ? `{{ url('/finance/settings/gl') }}/${id}` : `{{ route('finance.settings.gl.store') }}`;
      if(id) data._method='PUT';
      const response = await fetch(url, {
        method:'POST', 
        headers:{
          'X-CSRF-TOKEN':token,
          'Accept':'application/json'
        }, 
        body: toForm(data)
      });
      const result = await response.json();
      if(result.success){
        if (typeof window.AdvancedToast !== 'undefined') {
          window.AdvancedToast.success('Success', id ? 'GL Account updated successfully' : 'GL Account created successfully', { duration: 3000 });
        }
        setTimeout(() => location.reload(), 500);
      } else {
        throw new Error(result.message || 'Operation failed');
      }
    } catch(error){
      console.error('Error:', error);
      if (typeof window.AdvancedToast !== 'undefined') {
        window.AdvancedToast.error('Error', error.message || 'Failed to save GL Account', { duration: 5000 });
      } else {
        alert('Error: ' + (error.message || 'Failed to save GL Account'));
      }
      showLoading(false);
    }
  }
  
  async function submitCb(data, id){
    try {
      const url = id ? `{{ url('/finance/settings/cb') }}/${id}` : `{{ route('finance.settings.cb.store') }}`;
      if(id) data._method='PUT';
      const response = await fetch(url, {
        method:'POST', 
        headers:{
          'X-CSRF-TOKEN':token,
          'Accept':'application/json'
        }, 
        body: toForm(data)
      });
      const result = await response.json();
      if(result.success){
        if (typeof window.AdvancedToast !== 'undefined') {
          window.AdvancedToast.success('Success', id ? 'Cash Box updated successfully' : 'Cash Box created successfully', { duration: 3000 });
        }
        setTimeout(() => location.reload(), 500);
      } else {
        throw new Error(result.message || 'Operation failed');
      }
    } catch(error){
      console.error('Error:', error);
      if (typeof window.AdvancedToast !== 'undefined') {
        window.AdvancedToast.error('Error', error.message || 'Failed to save Cash Box', { duration: 5000 });
      } else {
        alert('Error: ' + (error.message || 'Failed to save Cash Box'));
      }
      showLoading(false);
    }
  }
  
  function toForm(obj){ 
    const f=new FormData(); 
    Object.entries(obj).forEach(([k,v])=>f.append(k,v)); 
    return f; 
  }
  function val(id){ 
    return document.getElementById(id)?.value || ''; 
  }
  function checked(id){ 
    return document.getElementById(id)?.checked || false; 
  }
  function escapeHtml(s){ 
    return (s||'').replace(/[&<>"']/g, m=>({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      '\'':'&#39;'
    }[m])); 
  }

  // Enhanced modal builder
  function modalPrompt(title, innerHtml, onSubmit){
    const backdrop = document.createElement('div');
    backdrop.className='modal fade show'; 
    backdrop.style.display='block'; 
    backdrop.style.background='rgba(0,0,0,.5)'; 
    backdrop.style.zIndex=1055;
    backdrop.style.position='fixed';
    backdrop.style.top='0';
    backdrop.style.left='0';
    backdrop.style.width='100%';
    backdrop.style.height='100%';
    
    const modal = document.createElement('div'); 
    modal.className='modal-dialog modal-dialog-centered'; 
    modal.innerHTML=`
      <div class="modal-content shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">
            <i class="bx bx-cog me-2"></i>${title}
          </h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ${innerHtml}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-cancel">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary btn-ok">
            <i class="bx bx-check me-1"></i>Save Changes
          </button>
        </div>
      </div>`;
    backdrop.appendChild(modal); 
    document.body.appendChild(backdrop);
    
    function close(){ 
      document.body.removeChild(backdrop); 
    }
    
    modal.querySelector('.btn-close').addEventListener('click', close);
    modal.querySelector('.btn-cancel').addEventListener('click', close);
    backdrop.addEventListener('click', function(e){
      if(e.target === backdrop) close();
    });
    
    modal.querySelector('.btn-ok').addEventListener('click', async ()=>{ 
      try {
        await onSubmit(); 
        close(); 
      } catch(e) {
        console.error('Submit error:', e);
      }
    });
  }
})();
</script>
@endpush
