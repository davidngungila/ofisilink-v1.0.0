@extends('layouts.app')

@section('title', 'Incidents Analytics')

@push('styles')
<style>
    .metric-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .chart-container {
        position: relative;
        height: 350px;
        margin-bottom: 2rem;
    }
    .chart-container-large {
        position: relative;
        height: 400px;
        margin-bottom: 2rem;
    }
    .stat-badge {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
</style>
@endpush

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
                                <i class="bx bx-bar-chart me-2"></i>Incidents Analytics
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Advanced analytics and reporting for incident management
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-calendar me-1"></i>Date From</label>
                            <input type="date" class="form-control" id="analyticsDateFrom" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-calendar me-1"></i>Date To</label>
                            <input type="date" class="form-control" id="analyticsDateTo" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" onclick="loadAnalytics()">
                                <i class="bx bx-refresh me-1"></i>Refresh Analytics
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Content -->
    <div id="analyticsContent">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading advanced analytics...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let chartInstances = {};

function destroyCharts() {
    Object.keys(chartInstances).forEach(key => {
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            delete chartInstances[key];
        }
    });
}

function loadAnalytics() {
    const dateFrom = document.getElementById('analyticsDateFrom').value;
    const dateTo = document.getElementById('analyticsDateTo').value;
    
    const analyticsContent = document.getElementById('analyticsContent');
    analyticsContent.innerHTML = `
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading advanced analytics...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Destroy existing charts
    destroyCharts();
    
    fetch('{{ route("modules.incidents.analytics.data") }}?date_from=' + dateFrom + '&date_to=' + dateTo, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            displayAnalytics(data.data);
        } else {
            analyticsContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error me-2"></i>Error loading analytics data.
                </div>
            `;
        }
    })
    .catch(err => {
        console.error('Error loading analytics:', err);
        analyticsContent.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error me-2"></i>Error loading analytics data.
            </div>
        `;
    });
}

function displayAnalytics(data) {
    const html = `
        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body text-center">
                        <i class="bx bx-list-ul fs-1 text-primary mb-2"></i>
                        <h3 class="mb-0 text-primary">${data.total_incidents || 0}</h3>
                        <p class="text-muted mb-0">Total Incidents</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body text-center">
                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                        <h3 class="mb-0 text-success">${data.resolved_count || 0}</h3>
                        <p class="text-muted mb-0">Resolved</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body text-center">
                        <i class="bx bx-time fs-1 text-warning mb-2"></i>
                        <h3 class="mb-0 text-warning">${data.open_count || 0}</h3>
                        <p class="text-muted mb-0">Open</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body text-center">
                        <i class="bx bx-trending-up fs-1 text-info mb-2"></i>
                        <h3 class="mb-0 text-info">${data.resolution_rate || 0}%</h3>
                        <p class="text-muted mb-0">Resolution Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Avg Resolution Time</small>
                                <h4 class="mb-0">${data.avg_resolution_time || 0} days</h4>
                            </div>
                            <i class="bx bx-calendar-check fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Avg Response Time</small>
                                <h4 class="mb-0">${data.avg_response_time || 0} hrs</h4>
                            </div>
                            <i class="bx bx-time-five fs-1 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Critical Priority</small>
                                <h4 class="mb-0">${data.critical_priority_count || 0}</h4>
                            </div>
                            <i class="bx bx-error-circle fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">High Priority</small>
                                <h4 class="mb-0">${data.high_priority_count || 0}</h4>
                            </div>
                            <i class="bx bx-error fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-pie-chart me-2"></i>Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-bar-chart me-2"></i>Priority Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2 me-2"></i>Category Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-time me-2"></i>Resolution Time Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="resolutionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trends Charts -->
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-line-chart me-2"></i>Monthly Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-large">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        ${Object.keys(data.daily_trends || {}).length > 0 ? `
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-line-chart me-2"></i>Daily Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-large">
                            <canvas id="dailyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}

        <!-- Status Over Time -->
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-area me-2"></i>Status Over Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-large">
                            <canvas id="statusOverTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Trends -->
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-line-chart me-2"></i>Priority Trends Over Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-large">
                            <canvas id="priorityTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-user-check me-2"></i>Top Assignees</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="topAssigneesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-user me-2"></i>Top Reporters</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="topReportersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('analyticsContent').innerHTML = html;
    
    // Wait a bit for DOM to render
    setTimeout(() => {
        renderCharts(data);
    }, 100);
}

function renderCharts(data) {
    // Status Distribution (Doughnut)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = data.status_distribution || {};
        const statusLabels = Object.keys(statusData);
        const statusValues = Object.values(statusData);
        const statusColors = {
            'New': '#0d6efd',
            'Assigned': '#17a2b8',
            'In Progress': '#ffc107',
            'Resolved': '#28a745',
            'Closed': '#6c757d',
            'Cancelled': '#dc3545',
            'Rejected': '#dc3545'
        };
        
        chartInstances.statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusLabels.map(s => statusColors[s] || '#6c757d'),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Priority Distribution (Bar)
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx) {
        const priorityData = data.priority_distribution || {};
        const priorityLabels = Object.keys(priorityData);
        const priorityValues = Object.values(priorityData);
        const priorityColors = {
            'Low': '#6c757d',
            'Medium': '#17a2b8',
            'High': '#ffc107',
            'Critical': '#dc3545'
        };
        
        chartInstances.priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: priorityLabels,
                datasets: [{
                    label: 'Incidents',
                    data: priorityValues,
                    backgroundColor: priorityLabels.map(p => priorityColors[p] || '#6c757d'),
                    borderColor: priorityLabels.map(p => priorityColors[p] || '#6c757d'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Category Distribution (Bar)
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryData = data.category_distribution || {};
        const categoryLabels = Object.keys(categoryData).map(c => c.charAt(0).toUpperCase() + c.slice(1));
        const categoryValues = Object.values(categoryData);
        
        chartInstances.categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Incidents',
                    data: categoryValues,
                    backgroundColor: [
                        '#0d6efd',
                        '#17a2b8',
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Resolution Time Distribution
    const resolutionTimeCtx = document.getElementById('resolutionTimeChart');
    if (resolutionTimeCtx) {
        const resolutionData = data.resolution_time_buckets || {};
        const resolutionLabels = Object.keys(resolutionData);
        const resolutionValues = Object.values(resolutionData);
        
        chartInstances.resolutionTimeChart = new Chart(resolutionTimeCtx, {
            type: 'bar',
            data: {
                labels: resolutionLabels,
                datasets: [{
                    label: 'Incidents',
                    data: resolutionValues,
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Monthly Trends
    const monthlyCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyCtx) {
        const monthlyData = data.monthly_trends || {};
        const monthlyLabels = Object.keys(monthlyData).sort();
        const monthlyValues = monthlyLabels.map(m => monthlyData[m] || 0);
        
        chartInstances.monthlyTrendsChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Incidents',
                    data: monthlyValues,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Daily Trends
    const dailyCtx = document.getElementById('dailyTrendsChart');
    if (dailyCtx && Object.keys(data.daily_trends || {}).length > 0) {
        const dailyData = data.daily_trends || {};
        const dailyLabels = Object.keys(dailyData).sort();
        const dailyValues = dailyLabels.map(d => dailyData[d] || 0);
        
        chartInstances.dailyTrendsChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Incidents',
                    data: dailyValues,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Status Over Time
    const statusOverTimeCtx = document.getElementById('statusOverTimeChart');
    if (statusOverTimeCtx) {
        const statusOverTimeData = data.status_over_time || {};
        const months = new Set();
        Object.values(statusOverTimeData).forEach(statusData => {
            Object.keys(statusData).forEach(month => months.add(month));
        });
        const sortedMonths = Array.from(months).sort();
        
        const statusColors = {
            'New': '#0d6efd',
            'Assigned': '#17a2b8',
            'In Progress': '#ffc107',
            'Resolved': '#28a745',
            'Closed': '#6c757d',
            'Rejected': '#dc3545'
        };
        
        const datasets = Object.keys(statusOverTimeData).map(status => ({
            label: status,
            data: sortedMonths.map(month => statusOverTimeData[status][month] || 0),
            borderColor: statusColors[status] || '#6c757d',
            backgroundColor: statusColors[status] ? statusColors[status] + '20' : '#6c757d20',
            tension: 0.4,
            fill: false
        }));
        
        chartInstances.statusOverTimeChart = new Chart(statusOverTimeCtx, {
            type: 'line',
            data: {
                labels: sortedMonths,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Priority Trends
    const priorityTrendsCtx = document.getElementById('priorityTrendsChart');
    if (priorityTrendsCtx) {
        const priorityTrendsData = data.priority_trends || {};
        const months = new Set();
        Object.values(priorityTrendsData).forEach(priorityData => {
            Object.keys(priorityData).forEach(month => months.add(month));
        });
        const sortedMonths = Array.from(months).sort();
        
        const priorityColors = {
            'Low': '#6c757d',
            'Medium': '#17a2b8',
            'High': '#ffc107',
            'Critical': '#dc3545'
        };
        
        const datasets = Object.keys(priorityTrendsData).map(priority => ({
            label: priority,
            data: sortedMonths.map(month => priorityTrendsData[priority][month] || 0),
            borderColor: priorityColors[priority] || '#6c757d',
            backgroundColor: priorityColors[priority] ? priorityColors[priority] + '20' : '#6c757d20',
            tension: 0.4,
            fill: false
        }));
        
        chartInstances.priorityTrendsChart = new Chart(priorityTrendsCtx, {
            type: 'line',
            data: {
                labels: sortedMonths,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Top Assignees
    const topAssigneesCtx = document.getElementById('topAssigneesChart');
    if (topAssigneesCtx && data.top_assignees && data.top_assignees.length > 0) {
        const assigneesData = data.top_assignees.slice(0, 10);
        const assigneesLabels = assigneesData.map(a => a.user);
        const assigneesValues = assigneesData.map(a => a.count);
        
        chartInstances.topAssigneesChart = new Chart(topAssigneesCtx, {
            type: 'bar',
            data: {
                labels: assigneesLabels,
                datasets: [{
                    label: 'Incidents',
                    data: assigneesValues,
                    backgroundColor: '#0d6efd',
                    borderColor: '#0d6efd',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Top Reporters
    const topReportersCtx = document.getElementById('topReportersChart');
    if (topReportersCtx && data.top_reporters && data.top_reporters.length > 0) {
        const reportersData = data.top_reporters.slice(0, 10);
        const reportersLabels = reportersData.map(r => r.user);
        const reportersValues = reportersData.map(r => r.count);
        
        chartInstances.topReportersChart = new Chart(topReportersCtx, {
            type: 'bar',
            data: {
                labels: reportersLabels,
                datasets: [{
                    label: 'Incidents',
                    data: reportersValues,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

// Load analytics on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        script.onload = function() {
            loadAnalytics();
        };
        document.head.appendChild(script);
    } else {
        loadAnalytics();
    }
});
</script>
@endpush
@endsection
