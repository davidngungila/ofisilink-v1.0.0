@extends('layouts.app')

@section('title', 'Leave Analytics - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stat-card.primary { border-left-color: #4e73df; }
    .stat-card.success { border-left-color: #1cc88a; }
    .stat-card.warning { border-left-color: #f6c23e; }
    .stat-card.danger { border-left-color: #e74a3b; }
    .stat-card.info { border-left-color: #36b9cc; }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 2rem;
    }
    .chart-container-large {
        height: 400px;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .analytics-tab {
        cursor: pointer;
    }
    .analytics-tab.active {
        background: #4e73df;
        color: white;
    }
    .border-left-primary { border-left: 4px solid #4e73df !important; }
    .border-left-success { border-left: 4px solid #1cc88a !important; }
    .border-left-warning { border-left: 4px solid #f6c23e !important; }
    .border-left-info { border-left: 4px solid #36b9cc !important; }
    .border-left-danger { border-left: 4px solid #e74a3b !important; }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa !important;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-bar-chart-alt-2 me-2"></i>Leave Analytics Dashboard
                            </h4>
                            <p class="card-text text-white-50 mb-0">Comprehensive insights and analytics for leave management</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light btn-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Leave Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body filter-section">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="bx bx-calendar me-1"></i>Financial Year</label>
                            <select class="form-select" id="filter-year">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bx bx-building me-1"></i>Department</label>
                            <select class="form-select" id="filter-department">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bx bx-list-ul me-1"></i>Leave Type</label>
                            <select class="form-select" id="filter-leave-type">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="loadAnalytics()">
                                <i class="bx bx-refresh me-1"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="stats-cards">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small mb-1">Total Requests</div>
                            <div class="stat-number text-primary" id="stat-total">0</div>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-file" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card success shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small mb-1">Completed</div>
                            <div class="stat-number text-success" id="stat-completed">0</div>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small mb-1">Active Requests</div>
                            <div class="stat-number text-warning" id="stat-active">0</div>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-time" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card info shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small mb-1">Avg Days</div>
                            <div class="stat-number text-info" id="stat-avg-days">0</div>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-calendar" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 text-white"><i class="bx bx-line-chart me-2"></i>Monthly Leave Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 text-white"><i class="bx bx-pie-chart me-2"></i>Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 text-white"><i class="bx bx-bar-chart me-2"></i>Department Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container chart-container-large">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 text-white"><i class="bx bx-bar-chart-alt me-2"></i>Leave Type Usage</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container chart-container-large">
                        <canvas id="leaveTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white"><i class="bx bx-table me-2"></i>Detailed Statistics</h6>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="exportAnalytics()">
                            <i class="bx bx-download me-1"></i>Export Data
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="analytics-content">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading analytics...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Fallback if SweetAlert2 isn't loaded
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        fire: function(optsOrTitle, text, icon) {
            if (typeof optsOrTitle === 'object') {
                const title = optsOrTitle.title || '';
                const html = optsOrTitle.html || optsOrTitle.text || '';
                alert(title + (html ? '\n\n' + html : ''));
                return Promise.resolve({ isConfirmed: true });
            } else {
                alert(optsOrTitle + (text ? '\n\n' + text : ''));
                return Promise.resolve({ isConfirmed: true });
            }
        },
        close: function() {},
        showLoading: function() {}
    };
}

let monthlyTrendChart, statusChart, departmentChart, leaveTypeChart;

// Load analytics function
function loadAnalytics() {
    const token = $('meta[name="csrf-token"]').attr('content');
    const year = $('#filter-year').val();
    const departmentId = $('#filter-department').val();
    const leaveTypeId = $('#filter-leave-type').val();
    
    $('#analytics-content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading analytics...</p></div>');
    
        $.ajax({
            url: '{{ route("leave.hr.analytics") }}',
        method: 'POST',
        data: {
            _token: token,
            year: year,
            department_id: departmentId,
            leave_type_id: leaveTypeId
        },
        success: function(response) {
            console.log('Analytics response:', response);
            if (response.success) {
                try {
                    updateStatistics(response);
                    updateCharts(response);
                    updateDetailedStats(response);
                } catch (error) {
                    console.error('Error updating analytics:', error);
                    $('#analytics-content').html('<div class="alert alert-danger">Error rendering analytics: ' + error.message + '</div>');
                }
            } else {
                $('#analytics-content').html('<div class="alert alert-danger">Error loading analytics: ' + (response.message || 'Unknown error') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
            let errorMsg = 'Failed to load analytics. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 0) {
                errorMsg = 'Network error. Please check your connection.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error. Please contact support.';
            }
            $('#analytics-content').html('<div class="alert alert-danger">' + errorMsg + '</div>');
        }
    });
}

// Update statistics cards
function updateStatistics(response) {
    const stats = response.stats || {};
    // Parse numeric values as Laravel may return them as strings
    const totalRequests = parseInt(stats.total_requests || stats['total_requests'] || 0, 10);
    const completedRequests = parseInt(stats.completed_requests || stats['completed_requests'] || 0, 10);
    const activeRequests = parseInt(stats.active_requests || stats['active_requests'] || 0, 10);
    const avgLeaveDays = parseFloat(stats.avg_leave_days || stats['avg_leave_days'] || 0);
    
    $('#stat-total').text(totalRequests);
    $('#stat-completed').text(completedRequests);
    $('#stat-active').text(activeRequests);
    $('#stat-avg-days').text(isNaN(avgLeaveDays) ? '0.0' : avgLeaveDays.toFixed(1));
}

// Update charts
function updateCharts(response) {
    console.log('=== Updating Leave Analytics Charts ===');
    console.log('Chart.js available:', typeof Chart !== 'undefined');
    
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded! Attempting to load from CDN...');
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        script.onload = function() {
            console.log('Chart.js loaded from CDN, updating charts...');
            updateCharts(response);
        };
        script.onerror = function() {
            console.error('Failed to load Chart.js from CDN');
            $('#analytics-content').prepend('<div class="alert alert-danger">Chart library could not be loaded. Please refresh the page.</div>');
        };
        document.head.appendChild(script);
        return;
    }
    
    // Monthly Trend Chart
    const monthlyData = response.monthly_trend || [];
    const monthlyLabels = monthlyData.map(item => item.month);
    const monthlyRequestCounts = monthlyData.map(item => item.request_count || 0);
    const monthlyCompletedDays = monthlyData.map(item => item.completed_days || 0);
    
    if (monthlyTrendChart) {
        monthlyTrendChart.destroy();
    }
    
    const monthlyCtxEl = document.getElementById('monthlyTrendChart');
    if (!monthlyCtxEl) {
        console.warn('Monthly trend chart canvas not found');
        return;
    }
    
    const monthlyCtx = monthlyCtxEl.getContext('2d');
    monthlyTrendChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Requests',
                data: monthlyRequestCounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Completed Days',
                data: monthlyCompletedDays,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            }
        }
    });
    
    // Status Distribution Chart
    const statusData = response.status_stats || [];
    const statusLabels = statusData.map(item => item.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
    const statusCounts = statusData.map(item => item.count || 0);
    
    if (statusChart) {
        statusChart.destroy();
    }
    
    const statusCtxEl = document.getElementById('statusChart');
    if (!statusCtxEl) {
        console.warn('Status chart canvas not found');
        return;
    }
    const statusCtx = statusCtxEl.getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // Department Statistics Chart
    const deptData = response.dept_stats || [];
    const deptLabels = deptData.map(item => item.department || 'Unknown');
    const deptRequestCounts = deptData.map(item => parseInt(item.request_count || 0, 10));
    const deptAvgDays = deptData.map(item => {
        const avg = parseFloat(item.avg_days || 0);
        return isNaN(avg) ? '0.0' : avg.toFixed(1);
    });
    
    if (departmentChart) {
        departmentChart.destroy();
    }
    
    const deptCtxEl = document.getElementById('departmentChart');
    if (!deptCtxEl) {
        console.warn('Department chart canvas not found');
        return;
    }
    const deptCtx = deptCtxEl.getContext('2d');
    departmentChart = new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'Requests',
                data: deptRequestCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Avg Days',
                data: deptAvgDays,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Request Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Days'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    // Leave Type Usage Chart
    const typeData = response.type_stats || [];
    const typeLabels = typeData.map(item => item.leave_type);
    const typeRequestCounts = typeData.map(item => parseInt(item.request_count || 0, 10));
    const typeAvgDays = typeData.map(item => {
        const avg = parseFloat(item.avg_days || 0);
        return isNaN(avg) ? '0.0' : avg.toFixed(1);
    });
    
    if (leaveTypeChart) {
        leaveTypeChart.destroy();
    }
    
    const typeCtxEl = document.getElementById('leaveTypeChart');
    if (!typeCtxEl) {
        console.warn('Leave type chart canvas not found');
        return;
    }
    const typeCtx = typeCtxEl.getContext('2d');
    leaveTypeChart = new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Requests',
                data: typeRequestCounts,
                backgroundColor: 'rgba(153, 102, 255, 0.8)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }, {
                label: 'Avg Days',
                data: typeAvgDays,
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Request Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Days'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    console.log('=== Leave Analytics Charts Updated Successfully ===');
    console.log('Monthly Trend Chart:', monthlyTrendChart ? 'Rendered' : 'Failed');
    console.log('Status Chart:', statusChart ? 'Rendered' : 'Failed');
    console.log('Department Chart:', departmentChart ? 'Rendered' : 'Failed');
    console.log('Leave Type Chart:', leaveTypeChart ? 'Rendered' : 'Failed');
}

// Update detailed statistics table
function updateDetailedStats(response) {
    try {
        let html = '';
        
        // Summary Cards Row
        const stats = response.stats || {};
        const approvalStats = response.approval_stats || {};
        
        // Check if we have any data at all
        // Laravel returns query results as objects, so check for null or if it's an empty object
        if (!stats || (typeof stats === 'object' && Object.keys(stats).length === 0 && stats.constructor === Object)) {
            html = '<div class="alert alert-info text-center py-4"><i class="bx bx-info-circle me-2"></i>No analytics data available for the selected filters.</div>';
            $('#analytics-content').html(html);
            return;
        }
        
        // Ensure stats properties are accessible (Laravel query results might need property access)
        const totalFareApproved = stats.total_fare_approved || stats['total_fare_approved'] || 0;
        const rejectedRequests = stats.rejected_requests || stats['rejected_requests'] || 0;
        const avgProcessingDays = approvalStats.avg_processing_days || approvalStats['avg_processing_days'] || 0;
        const maxProcessingDays = approvalStats.max_processing_days || approvalStats['max_processing_days'] || 0;
    html += '<div class="row mb-4">';
    html += '<div class="col-md-3 mb-3">';
    html += '<div class="card border-left-info h-100">';
    html += '<div class="card-body">';
    html += '<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Fare Approved</div>';
    html += `<div class="h5 mb-0 font-weight-bold text-gray-800">${parseFloat(totalFareApproved).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} TZS</div>`;
    html += '</div></div></div>';
    
    html += '<div class="col-md-3 mb-3">';
    html += '<div class="card border-left-warning h-100">';
    html += '<div class="card-body">';
    html += '<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rejected Requests</div>';
    html += `<div class="h5 mb-0 font-weight-bold text-gray-800">${rejectedRequests}</div>`;
    html += '</div></div></div>';
    
    html += '<div class="col-md-3 mb-3">';
    html += '<div class="card border-left-success h-100">';
    html += '<div class="card-body">';
    html += '<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg Processing Time</div>';
    html += `<div class="h5 mb-0 font-weight-bold text-gray-800">${parseFloat(avgProcessingDays).toFixed(1)} days</div>`;
    html += '</div></div></div>';
    
    html += '<div class="col-md-3 mb-3">';
    html += '<div class="card border-left-primary h-100">';
    html += '<div class="card-body">';
    html += '<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Max Processing Time</div>';
    html += `<div class="h5 mb-0 font-weight-bold text-gray-800">${maxProcessingDays} days</div>`;
    html += '</div></div></div>';
    html += '</div>';
    
    // Main Statistics Tables Row
    html += '<div class="row mb-4">';
    
    // Department Statistics Table
    html += '<div class="col-md-6 mb-4">';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-header bg-primary text-white">';
    html += '<h6 class="mb-0 text-white"><i class="bx bx-building me-2"></i>Department Statistics</h6>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="table-responsive" style="max-height: 400px;">';
    html += '<table class="table table-sm table-hover table-bordered mb-0">';
    html += '<thead class="table-light sticky-top"><tr><th>Department</th><th class="text-center">Requests</th><th class="text-center">Avg Days</th><th class="text-center">Total Days</th></tr></thead>';
    html += '<tbody>';
    
    const deptStats = response.dept_stats || [];
    if (deptStats.length > 0) {
        deptStats.forEach((dept, index) => {
            const requestCount = parseInt(dept.request_count || 0, 10);
            const avgDays = parseFloat(dept.avg_days || 0);
            const totalDays = requestCount * avgDays;
            html += `<tr>
                <td><strong>${index + 1}.</strong> ${dept.department || 'Unknown'}</td>
                <td class="text-center"><span class="badge bg-primary">${requestCount}</span></td>
                <td class="text-center">${isNaN(avgDays) ? '0.0' : avgDays.toFixed(1)}</td>
                <td class="text-center"><span class="badge bg-info">${isNaN(totalDays) ? '0' : totalDays.toFixed(0)}</span></td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bx bx-info-circle me-1"></i>No department data available</td></tr>';
    }
    
    html += '</tbody></table></div></div></div></div>';
    
    // Leave Type Statistics Table
    html += '<div class="col-md-6 mb-4">';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-header bg-primary text-white">';
    html += '<h6 class="mb-0 text-white"><i class="bx bx-list-ul me-2"></i>Leave Type Statistics</h6>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="table-responsive" style="max-height: 400px;">';
    html += '<table class="table table-sm table-hover table-bordered mb-0">';
    html += '<thead class="table-light sticky-top"><tr><th>Leave Type</th><th class="text-center">Requests</th><th class="text-center">Avg Days</th><th class="text-center">Total Days</th></tr></thead>';
    html += '<tbody>';
    
    const typeStats = response.type_stats || [];
    if (typeStats.length > 0) {
        typeStats.forEach((type, index) => {
            const requestCount = parseInt(type.request_count || 0, 10);
            const avgDays = parseFloat(type.avg_days || 0);
            const totalDays = requestCount * avgDays;
            html += `<tr>
                <td><strong>${index + 1}.</strong> ${type.leave_type}</td>
                <td class="text-center"><span class="badge bg-info">${requestCount}</span></td>
                <td class="text-center">${isNaN(avgDays) ? '0.0' : avgDays.toFixed(1)}</td>
                <td class="text-center"><span class="badge bg-success">${isNaN(totalDays) ? '0' : totalDays.toFixed(0)}</span></td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bx bx-info-circle me-1"></i>No leave type data available</td></tr>';
    }
    
    html += '</tbody></table></div></div></div></div>';
    html += '</div>';
    
    // Employee Statistics and Top Requesters Row
    html += '<div class="row mb-4">';
    
    // Employee-Level Statistics
    html += '<div class="col-md-6 mb-4">';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-header bg-primary text-white">';
    html += '<h6 class="mb-0 text-white"><i class="bx bx-user me-2"></i>Employee Leave Statistics (Top 20)</h6>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="table-responsive" style="max-height: 500px;">';
    html += '<table class="table table-sm table-hover table-bordered mb-0">';
    html += '<thead class="table-light sticky-top"><tr><th>#</th><th>Employee</th><th>Dept</th><th class="text-center">Requests</th><th class="text-center">Total Days</th><th class="text-center">Completed</th></tr></thead>';
    html += '<tbody>';
    
    const employeeStats = response.employee_stats || [];
    if (employeeStats.length > 0) {
        employeeStats.forEach((emp, index) => {
            const totalDaysTaken = parseFloat(emp.total_days_taken || 0);
            html += `<tr>
                <td><strong>${index + 1}</strong></td>
                <td>${emp.employee_name || 'Unknown'}</td>
                <td><small class="text-muted">${emp.department_name || 'N/A'}</small></td>
                <td class="text-center"><span class="badge bg-primary">${parseInt(emp.request_count || 0, 10)}</span></td>
                <td class="text-center"><span class="badge bg-warning">${isNaN(totalDaysTaken) ? '0' : totalDaysTaken.toFixed(0)}</span></td>
                <td class="text-center"><span class="badge bg-success">${parseInt(emp.completed_count || 0, 10)}</span></td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bx bx-info-circle me-1"></i>No employee data available</td></tr>';
    }
    
    html += '</tbody></table></div></div></div></div>';
    
    // Status Breakdown Table
    html += '<div class="col-md-6 mb-4">';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-header bg-primary text-white">';
    html += '<h6 class="mb-0 text-white"><i class="bx bx-pie-chart me-2"></i>Status Breakdown</h6>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="table-responsive" style="max-height: 500px;">';
    html += '<table class="table table-sm table-hover table-bordered mb-0">';
    html += '<thead class="table-light sticky-top"><tr><th>Status</th><th class="text-center">Count</th><th class="text-center">Percentage</th></tr></thead>';
    html += '<tbody>';
    
    const statusStats = response.status_stats || [];
    const totalRequests = parseInt(stats.total_requests || stats['total_requests'] || 1, 10);
    if (statusStats.length > 0) {
        statusStats.forEach(status => {
            const count = parseInt(status.count || 0, 10);
            const percentage = totalRequests > 0 ? ((count / totalRequests) * 100).toFixed(1) : '0.0';
            const statusLabel = (status.status || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            let badgeClass = 'bg-secondary';
            if (status.status === 'completed') badgeClass = 'bg-success';
            else if (status.status === 'rejected' || status.status === 'cancelled') badgeClass = 'bg-danger';
            else if (status.status?.includes('pending')) badgeClass = 'bg-warning';
            else if (status.status === 'on_leave') badgeClass = 'bg-info';
            
            html += `<tr>
                <td><span class="badge ${badgeClass} me-2">${statusLabel}</span></td>
                <td class="text-center"><strong>${status.count || 0}</strong></td>
                <td class="text-center">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${badgeClass}" role="progressbar" style="width: ${percentage}%">
                            ${percentage}%
                        </div>
                    </div>
                </td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="3" class="text-center text-muted py-4"><i class="bx bx-info-circle me-1"></i>No status data available</td></tr>';
    }
    
    html += '</tbody></table></div></div></div></div>';
    html += '</div>';
    
    // Monthly Status Breakdown
    html += '<div class="row mb-4">';
    html += '<div class="col-12">';
    html += '<div class="card shadow-sm">';
    html += '<div class="card-header bg-primary text-white">';
    html += '<h6 class="mb-0 text-white"><i class="bx bx-calendar me-2"></i>Monthly Status Breakdown</h6>';
    html += '</div>';
    html += '<div class="card-body">';
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm table-hover table-bordered mb-0">';
    html += '<thead class="table-light"><tr><th>Month</th><th>Status</th><th class="text-center">Count</th><th class="text-center">Total Days</th></tr></thead>';
    html += '<tbody>';
    
    const monthlyStatus = response.monthly_status_breakdown || [];
    if (monthlyStatus.length > 0) {
        let currentMonth = '';
        monthlyStatus.forEach(item => {
            const monthLabel = item.month !== currentMonth ? `<strong>${item.month}</strong>` : '';
            currentMonth = item.month;
            const statusLabel = (item.status || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            let badgeClass = 'bg-secondary';
            if (item.status === 'completed') badgeClass = 'bg-success';
            else if (item.status === 'rejected' || item.status === 'cancelled') badgeClass = 'bg-danger';
            else if (item.status?.includes('pending')) badgeClass = 'bg-warning';
            else if (item.status === 'on_leave') badgeClass = 'bg-info';
            
            const totalDays = parseFloat(item.total_days || 0);
            html += `<tr>
                <td>${monthLabel}</td>
                <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                <td class="text-center">${parseInt(item.count || 0, 10)}</td>
                <td class="text-center">${isNaN(totalDays) ? '0' : totalDays.toFixed(0)}</td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bx bx-info-circle me-1"></i>No monthly breakdown data available</td></tr>';
    }
    
    html += '</tbody></table></div></div></div></div>';
    html += '</div>';
    
    $('#analytics-content').html(html);
    } catch (error) {
        console.error('Error in updateDetailedStats:', error);
        $('#analytics-content').html('<div class="alert alert-danger">Error rendering detailed statistics: ' + error.message + '</div>');
    }
}

// Export analytics
function exportAnalytics() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Export Analytics',
            text: 'Export functionality will be implemented soon.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    } else {
        alert('Export functionality will be implemented soon.');
    }
}

// Refresh analytics
function refreshAnalytics() {
    loadAnalytics();
}

// Load analytics on page load
$(document).ready(function() {
    loadAnalytics();
    
    // Auto-refresh on filter change
    $('#filter-year, #filter-department, #filter-leave-type').on('change', function() {
        // Optional: auto-refresh on filter change
        // loadAnalytics();
    });
});
</script>
@endpush
