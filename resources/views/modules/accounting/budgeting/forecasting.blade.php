@extends('layouts.app')

@section('title', 'Financial Forecasting')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Budgeting & Forecasting - Financial Forecasting</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="forecasted"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="average"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="growth"] {
        border-left-color: #28a745 !important;
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

    #forecastChart {
        max-height: 400px;
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
                        <i class="bx bx-trending-up me-2"></i>Financial Forecasting
                    </h2>
                    <p class="mb-0 opacity-90">Generate financial projections and forecasts</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button class="btn btn-light btn-sm" id="btn-export-excel" title="Export Excel">
                        <i class="bx bxs-file-excel me-1"></i>Excel
                    </button>
                    <a class="btn btn-secondary btn-sm" id="btn-export" target="_blank" title="Export PDF">
                        <i class="bx bxs-file-pdf me-1"></i>PDF
                    </a>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="forecasted">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Forecasted</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumForecasted">0.00</h3>
                            <small class="text-muted" id="sumPeriod">Over 0 months</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="average">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Average Monthly</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumAverage">0.00</h3>
                            <small class="text-muted">Per month</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-calendar fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="growth">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Growth Rate</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumGrowth">0.00%</h3>
                            <small class="text-muted">Projected growth</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-up fs-4 text-success"></i>
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
                    <i class="bx bx-filter-alt me-2"></i>Forecast Parameters
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
                        <label class="form-label small text-muted fw-semibold">Forecast Type</label>
                        <select class="form-select form-select-sm" id="filterType">
                            <option value="revenue" selected>Revenue</option>
                            <option value="expense">Expense</option>
                            <option value="cash_flow">Cash Flow</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Forecast Period (Months)</label>
                        <input type="number" class="form-control form-control-sm" id="filterPeriod" value="12" min="1" max="60">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Forecast Method</label>
                        <select class="form-select form-select-sm" id="filterMethod">
                            <option value="trend" selected>Trend Analysis</option>
                            <option value="moving_average">Moving Average</option>
                            <option value="exponential_smoothing">Exponential Smoothing</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btn-generate">
                            <i class="bx bx-calculator me-1"></i>Generate Forecast
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Chart -->
    <div class="card border-0 shadow-sm mb-4" id="chartCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-line-chart me-2"></i>Forecast Visualization
            </h6>
        </div>
        <div class="card-body">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>

    <!-- Forecast Table -->
    <div class="card border-0 shadow-sm mb-4" id="tableCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-table me-2"></i>Forecast Details
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="forecastTable">
                    <thead class="table-light">
                        <tr>
                            <th>Period</th>
                            <th class="text-end">Forecasted Amount</th>
                            <th class="text-end">Lower Bound (95%)</th>
                            <th class="text-end">Upper Bound (95%)</th>
                        </tr>
                    </thead>
                    <tbody id="forecastTableBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Account-wise Forecasts -->
    <div class="card border-0 shadow-sm" id="accountsCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-list-ul me-2"></i>Account-wise Forecasts
            </h6>
        </div>
        <div class="card-body">
            <div class="accordion" id="accountForecastsAccordion">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Loading/Empty State -->
    <div id="emptyState" class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-line-chart fs-1 text-muted"></i>
            <p class="text-muted mt-2 mb-0">Configure forecast parameters and click "Generate Forecast" to view projections</p>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Advanced Forecasting Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.budgeting.forecasting.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.budgeting.forecasting') }}';
    let forecastChart = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            type: document.getElementById('filterType')?.value || 'revenue',
            period: parseInt(document.getElementById('filterPeriod')?.value || 12),
            method: document.getElementById('filterMethod')?.value || 'trend'
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const forecasted = summary?.total_forecasted || 0;
        const average = summary?.average_monthly || 0;
        const growth = summary?.growth_rate || 0;
        const period = summary?.period || 0;
        
        animateValue('sumForecasted', 0, forecasted, 800);
        animateValue('sumAverage', 0, average, 800);
        
        if(document.getElementById('sumGrowth')) {
            document.getElementById('sumGrowth').textContent = growth.toFixed(2) + '%';
        }
        if(document.getElementById('sumPeriod')) {
            document.getElementById('sumPeriod').textContent = `Over ${period} months`;
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
    
    function renderChart(forecast, historical, confidenceIntervals){
        const ctx = document.getElementById('forecastChart');
        if(!ctx || typeof Chart === 'undefined') return;
        
        if(forecastChart) {
            forecastChart.destroy();
        }
        
        const historicalLabels = historical.map(d => {
            const date = new Date(d.date + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        const historicalAmounts = historical.map(d => parseFloat(d.amount));
        
        const forecastLabels = forecast.map(d => {
            const date = new Date(d.date + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        const forecastAmounts = forecast.map(d => parseFloat(d.amount));
        const lowerBounds = confidenceIntervals.map(c => parseFloat(c.lower));
        const upperBounds = confidenceIntervals.map(c => parseFloat(c.upper));
        
        forecastChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [...historicalLabels, ...forecastLabels],
                datasets: [{
                    label: 'Historical',
                    data: [...historicalAmounts, ...new Array(forecastLabels.length).fill(null)],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                }, {
                    label: 'Forecast',
                    data: [...new Array(historicalLabels.length).fill(null), ...forecastAmounts],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderDash: [5, 5],
                    tension: 0.4
                }, {
                    label: 'Upper Bound (95%)',
                    data: [...new Array(historicalLabels.length).fill(null), ...upperBounds],
                    borderColor: 'rgba(255, 99, 132, 0.3)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderDash: [2, 2],
                    fill: false
                }, {
                    label: 'Lower Bound (95%)',
                    data: [...new Array(historicalLabels.length).fill(null), ...lowerBounds],
                    borderColor: 'rgba(255, 99, 132, 0.3)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderDash: [2, 2],
                    fill: '-1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }
    
    function renderTable(forecast, confidenceIntervals){
        const tbody = document.getElementById('forecastTableBody');
        if(!tbody) return;
        
        if(!forecast || !forecast.length){
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">No forecast data available</td></tr>';
            return;
        }
        
        tbody.innerHTML = forecast.map((item, idx) => {
            const date = new Date(item.date + '-01');
            const confidence = confidenceIntervals[idx] || null;
            
            return `
                <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                    <td><strong>${date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })}</strong></td>
                    <td class="text-end">
                        <strong class="text-primary">${formatCurrency(item.amount)}</strong>
                    </td>
                    <td class="text-end">
                        ${confidence ? `<small class="text-muted">${formatCurrency(confidence.lower)}</small>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td class="text-end">
                        ${confidence ? `<small class="text-muted">${formatCurrency(confidence.upper)}</small>` : '<span class="text-muted">-</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function renderAccountForecasts(accountForecasts){
        const accordion = document.getElementById('accountForecastsAccordion');
        if(!accordion) return;
        
        if(!accountForecasts || !accountForecasts.length){
            accordion.innerHTML = '<div class="text-center py-3 text-muted">No account forecasts available</div>';
            return;
        }
        
        accordion.innerHTML = accountForecasts.map((accountForecast, idx) => {
            const account = accountForecast;
            const forecast = accountForecast.forecast || [];
            
            return `
                <div class="accordion-item fade-in" style="animation-delay: ${idx * 0.05}s">
                    <h2 class="accordion-header">
                        <button class="accordion-button ${idx > 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${idx}">
                            ${escapeHtml(account.account_code)} - ${escapeHtml(account.account_name)}
                        </button>
                    </h2>
                    <div id="collapse${idx}" class="accordion-collapse collapse ${idx == 0 ? 'show' : ''}" data-bs-parent="#accountForecastsAccordion">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th class="text-end">Forecasted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${forecast.map(fc => {
                                            const date = new Date(fc.date + '-01');
                                            return `
                                                <tr>
                                                    <td>${date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })}</td>
                                                    <td class="text-end">${formatCurrency(fc.amount)}</td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    function load(){
        showLoading(true);
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('chartCard').style.display = 'none';
        document.getElementById('tableCard').style.display = 'none';
        document.getElementById('accountsCard').style.display = 'none';
        
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
                let msg = res.message || 'Failed to load forecast';
                
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
            
            updateSummary(res.summary || {});
            
            if(res.forecast && res.forecast.length > 0){
                document.getElementById('chartCard').style.display = 'block';
                document.getElementById('tableCard').style.display = 'block';
                document.getElementById('accountsCard').style.display = 'block';
                
                renderChart(res.forecast || [], res.historical_data || [], res.confidence_intervals || []);
                renderTable(res.forecast || [], res.confidence_intervals || []);
                renderAccountForecasts(res.account_forecasts || []);
            } else {
                document.getElementById('emptyState').style.display = 'block';
            }
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
        document.getElementById('btn-export').addEventListener('click', (e) => {
            e.preventDefault();
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key]) {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'pdf');
            window.open(pdfEndpoint + '?' + p.toString(), '_blank');
        });
    }
    
    if(document.getElementById('btn-export-excel')) {
        document.getElementById('btn-export-excel').addEventListener('click', () => {
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key]) {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'excel');
            window.location.href = pdfEndpoint + '?' + p.toString();
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
    
    // Make load function globally accessible
    window.loadForecasting = function() {
        load();
    };
    
    // Don't auto-load on page load - wait for user to click Generate
})();
</script>
@endpush
