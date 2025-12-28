@extends('layouts.app')

@section('title', 'Tax Reports')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Tax Reports</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="vat"] {
        border-left-color: #1976d2 !important;
    }

    .summary-card[data-type="gst"] {
        border-left-color: #7b1fa2 !important;
    }

    .summary-card[data-type="wht"] {
        border-left-color: #e65100 !important;
    }

    .summary-card[data-type="paye"] {
        border-left-color: #388e3c !important;
    }

    .summary-card[data-type="summary"] {
        border-left-color: #c2185b !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
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
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>Tax Return Reports
                    </h2>
                    <p class="mb-0 opacity-90">Comprehensive tax analysis and reporting</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('modules.accounting.taxation.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-cog me-1"></i>Tax Settings
                    </a>
                    <button class="btn btn-light btn-sm" id="btn-export" title="Export PDF">
                        <i class="bx bxs-file-pdf me-1"></i>PDF
                    </button>
                    <button class="btn btn-light btn-sm" id="btn-refresh" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-calendar me-2"></i>Report Period
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterStartDate" value="{{ now()->startOfYear()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterEndDate" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                    <button class="btn btn-primary btn-sm w-100" id="btn-generate">
                        <i class="bx bx-calculator me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Summary Cards -->
    <div class="row g-3 mb-4" id="summaryCards">
        <!-- VAT Summary -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="vat">
                <div class="card-body">
                    <h6 class="text-muted mb-3">VAT (Value Added Tax)</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Collected</span>
                        <strong class="text-primary" id="vatCollected">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Paid</span>
                        <strong class="text-info" id="vatPaid">0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Net VAT</span>
                        <strong class="fw-bold" id="vatNet">0.00</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- GST Summary -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="gst">
                <div class="card-body">
                    <h6 class="text-muted mb-3">GST (Goods & Services Tax)</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Collected</span>
                        <strong class="text-primary" id="gstCollected">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Paid</span>
                        <strong class="text-info" id="gstPaid">0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Net GST</span>
                        <strong class="fw-bold" id="gstNet">0.00</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withholding Tax -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="wht">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Withholding Tax</h6>
                    <div class="text-center py-3">
                        <h3 class="mb-0 text-warning" id="whtTotal">0.00</h3>
                        <small class="text-muted">Total WHT Deducted</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYE and Overall Summary -->
    <div class="row g-3 mb-4" id="payeSummary">
        <!-- PAYE Summary -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="paye">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">PAYE (Pay As You Earn)</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-2 mb-3">
                        <h2 class="mb-0 text-success" id="payeTotal">0.00</h2>
                        <small class="text-muted">Total PAYE Collected</small>
                    </div>
                    <div id="payeDetailsContainer">
                        <!-- PAYE details will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Summary -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="summary">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Overall Tax Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Tax Collected:</span>
                            <strong class="text-primary" id="totalTaxCollected">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Tax Paid:</span>
                            <strong class="text-info" id="totalTaxPaid">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>PAYE Collected:</span>
                            <strong class="text-success" id="payeTotalSummary">0.00</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold h6 mb-0">Net Tax Owed:</span>
                        <strong class="h5 fw-bold" id="totalTaxOwed">0.00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Period Info -->
    <div class="alert alert-info" id="reportPeriodInfo" style="display: none;">
        <i class="bx bx-info-circle"></i>
        <strong>Report Period:</strong> <span id="periodText">-</span>
    </div>

    <!-- Loading/Empty State -->
    <div id="emptyState" class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-line-chart fs-1 text-muted"></i>
            <p class="text-muted mt-2 mb-0">Select date range and click "Generate Report" to view tax reports</p>
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
// Advanced Tax Reports Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.taxation.reports.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.taxation.reports.export') }}';
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            start_date: document.getElementById('filterStartDate')?.value || '{{ now()->startOfYear()->format('Y-m-d') }}',
            end_date: document.getElementById('filterEndDate')?.value || '{{ now()->format('Y-m-d') }}'
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary, payeDetails, filters){
        const vatCollected = summary?.vat_collected || 0;
        const vatPaid = summary?.vat_paid || 0;
        const vatNet = summary?.vat_net || 0;
        const gstCollected = summary?.gst_collected || 0;
        const gstPaid = summary?.gst_paid || 0;
        const gstNet = summary?.gst_net || 0;
        const whtTotal = summary?.wht_total || 0;
        const payeTotal = summary?.paye_total || 0;
        const totalTaxCollected = summary?.total_tax_collected || 0;
        const totalTaxPaid = summary?.total_tax_paid || 0;
        const totalTaxOwed = summary?.total_tax_owed || 0;
        
        animateValue('vatCollected', 0, vatCollected, 800);
        animateValue('vatPaid', 0, vatPaid, 800);
        animateValue('vatNet', 0, Math.abs(vatNet), 800);
        animateValue('gstCollected', 0, gstCollected, 800);
        animateValue('gstPaid', 0, gstPaid, 800);
        animateValue('gstNet', 0, Math.abs(gstNet), 800);
        animateValue('whtTotal', 0, whtTotal, 800);
        animateValue('payeTotal', 0, payeTotal, 800);
        animateValue('totalTaxCollected', 0, totalTaxCollected, 800);
        animateValue('totalTaxPaid', 0, totalTaxPaid, 800);
        animateValue('totalTaxOwed', 0, Math.abs(totalTaxOwed), 800);
        
        // Update VAT Net color
        const vatNetEl = document.getElementById('vatNet');
        if(vatNetEl) {
            vatNetEl.className = vatNet >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
        }
        
        // Update GST Net color
        const gstNetEl = document.getElementById('gstNet');
        if(gstNetEl) {
            gstNetEl.className = gstNet >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
        }
        
        // Update Total Tax Owed color
        const totalTaxOwedEl = document.getElementById('totalTaxOwed');
        if(totalTaxOwedEl) {
            totalTaxOwedEl.className = totalTaxOwed >= 0 ? 'h5 fw-bold text-danger' : 'h5 fw-bold text-success';
        }
        
        if(document.getElementById('payeTotalSummary')) {
            document.getElementById('payeTotalSummary').textContent = formatCurrency(payeTotal);
        }
        
        // Render PAYE Details
        renderPayeDetails(payeDetails || []);
        
        // Update period text
        if(filters && filters.start_date && filters.end_date) {
            const start = new Date(filters.start_date);
            const end = new Date(filters.end_date);
            if(document.getElementById('periodText')) {
                document.getElementById('periodText').textContent = 
                    start.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + 
                    ' to ' + 
                    end.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            }
            if(document.getElementById('reportPeriodInfo')) {
                document.getElementById('reportPeriodInfo').style.display = 'block';
            }
        }
    }
    
    function animateValue(id, start, end, duration){
        const element = document.getElementById(id);
        if(!element) return;
        const startVal = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || start;
        let current = startVal;
        const range = end - startVal;
        const increment = range / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                element.textContent = formatCurrency(end);
                clearInterval(timer);
            } else {
                element.textContent = formatCurrency(current);
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
    
    function renderPayeDetails(payeDetails){
        const container = document.getElementById('payeDetailsContainer');
        if(!container) return;
        
        if(!payeDetails || !payeDetails.length){
            container.innerHTML = '<p class="text-muted text-center mb-0"><small>No PAYE details available</small></p>';
            return;
        }
        
        let html = '<hr><div class="mt-3"><small class="text-muted d-block mb-2">Breakdown by Payroll Period:</small>';
        html += '<div class="table-responsive"><table class="table table-sm table-borderless mb-0">';
        html += '<thead><tr><th>Period</th><th>Employees</th><th class="text-end">PAYE</th></tr></thead><tbody>';
        
        payeDetails.forEach((detail, idx) => {
            html += `
                <tr class="fade-in" style="animation-delay: ${idx * 0.05}s">
                    <td>${escapeHtml(detail.pay_period || '')}</td>
                    <td>${detail.employee_count || 0}</td>
                    <td class="text-end">${formatCurrency(detail.paye_amount || 0)}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }
    
    function load(){
        showLoading(true);
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('summaryCards').style.display = 'none';
        document.getElementById('payeSummary').style.display = 'none';
        
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
                let msg = res.message || 'Failed to load tax reports';
                
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
                
                document.getElementById('emptyState').innerHTML = `
                    <div class="card-body text-danger text-center py-5">
                        <i class="bx bx-error-circle fs-1"></i>
                        <div class="mt-2">${msg.includes('<br>') ? msg : escapeHtml(msg)}</div>
                    </div>
                `;
                document.getElementById('emptyState').style.display = 'block';
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            document.getElementById('summaryCards').style.display = 'flex';
            document.getElementById('payeSummary').style.display = 'flex';
            
            updateSummary(res.summary || {}, res.paye_details || [], res.filters || {});
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            document.getElementById('emptyState').innerHTML = `
                <div class="card-body text-danger text-center py-5">
                    <i class="bx bx-error-circle fs-1"></i>
                    <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                </div>
            `;
            document.getElementById('emptyState').style.display = 'block';
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    if(document.getElementById('btn-refresh')) {
        document.getElementById('btn-refresh').addEventListener('click', () => { load(); });
    }
    
    if(document.getElementById('btn-generate')) {
        document.getElementById('btn-generate').addEventListener('click', () => { load(); });
    }
    
    if(document.getElementById('btn-export')) {
        document.getElementById('btn-export').addEventListener('click', () => {
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key]) {
                    p.append(key, params[key]);
                }
            });
            window.open(pdfEndpoint + '?' + p.toString(), '_blank');
        });
    }
    
    // Make load function globally accessible
    window.loadTaxReports = function() {
        load();
    };
    
    // Don't auto-load on page load - wait for user to click Generate
})();
</script>
@endpush
