<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bx bx-bar-chart me-2"></i>Advanced Incident Analytics & Reports
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height: calc(100vh - 120px); overflow-y: auto;">
        <div class="row mb-4 sticky-top bg-white p-3 shadow-sm" style="z-index: 10;">
          <div class="col-md-4">
            <label class="form-label"><i class="bx bx-calendar me-1"></i>Date From</label>
            <input type="date" class="form-control" id="analyticsDateFrom" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label"><i class="bx bx-calendar me-1"></i>Date To</label>
            <input type="date" class="form-control" id="analyticsDateTo" value="{{ date('Y-m-d') }}">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100" onclick="loadAnalytics()">
          <i class="bx bx-refresh me-1"></i>Refresh Analytics
        </button>
          </div>
        </div>
        <div id="analyticsContent">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading advanced analytics...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" onclick="exportAnalytics()">
          <i class="bx bx-download me-1"></i>Export Report
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.analytics-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
  border-radius: 8px;
}
.analytics-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}
.chart-container {
  position: relative;
  height: 300px;
  min-height: 300px;
  width: 100%;
  padding: 10px;
}
.chart-container-lg {
  position: relative;
  height: 400px;
  min-height: 400px;
  width: 100%;
  padding: 10px;
}
.stat-card {
  border-left: 4px solid;
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.stat-card.primary { border-left-color: #0d6efd; }
.stat-card.success { border-left-color: #198754; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.danger { border-left-color: #dc3545; }
.stat-card.info { border-left-color: #0dcaf0; }
.stat-card.secondary { border-left-color: #6c757d; }
.card-header {
  border-bottom: 2px solid rgba(0,0,0,0.1);
  font-weight: 600;
  padding: 12px 20px;
}
.card-body {
  padding: 20px;
}
#analyticsContent {
  padding: 10px 0;
}
.shadow-sm {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}
</style>

<script>
let chartInstances = [];

function destroyCharts() {
  chartInstances.forEach(chart => {
    if (chart && typeof chart.destroy === 'function') {
      chart.destroy();
    }
  });
  chartInstances = [];
}

function loadAnalytics() {
  const dateFrom = document.getElementById('analyticsDateFrom').value;
  const dateTo = document.getElementById('analyticsDateTo').value;
  const content = document.getElementById('analyticsContent');
  
  if (!dateFrom || !dateTo) {
    content.innerHTML = '<div class="alert alert-warning">Please select both date ranges.</div>';
    return;
  }
  
  content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading advanced analytics...</p></div>';
  
  // Destroy existing charts
  destroyCharts();
  
  fetch(`{{ route("modules.incidents.analytics") }}?date_from=${dateFrom}&date_to=${dateTo}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    }
  })
  .then(async res => {
    console.log('Response status:', res.status);
    if (!res.ok) {
      const errorText = await res.text();
      console.error('Error response:', errorText);
      throw new Error(`HTTP ${res.status}: ${errorText || res.statusText}`);
    }
    const jsonData = await res.json();
    console.log('Raw JSON response:', jsonData);
    return jsonData;
  })
  .then(data => {
    console.log('Analytics data received:', data);
    if (data.success && data.data) {
      const d = data.data;
      console.log('Analytics data object:', d);
      console.log('Status distribution:', d.status_distribution);
      console.log('Priority distribution:', d.priority_distribution);
      console.log('Category distribution:', d.category_distribution);
      console.log('Total incidents:', d.total_incidents);
      renderAdvancedAnalytics(d);
    } else {
      console.error('Invalid response structure:', data);
      const errorMsg = data.message || 'Unknown error occurred';
      document.getElementById('analyticsContent').innerHTML = `
        <div class="alert alert-danger">
          <h6><i class="bx bx-error-circle me-2"></i>Error loading analytics</h6>
          <p class="mb-0">${errorMsg}</p>
          <small>Response: ${JSON.stringify(data)}</small>
        </div>`;
    }
  })
  .catch(err => {
    console.error('Analytics fetch error:', err);
    console.error('Error stack:', err.stack);
    const content = document.getElementById('analyticsContent');
    if (content) {
      content.innerHTML = `
        <div class="alert alert-danger">
          <h6><i class="bx bx-error-circle me-2"></i>Error loading analytics</h6>
          <p class="mb-2"><strong>Error:</strong> ${err.message}</p>
          <small>Please check the browser console (F12) for more details.</small>
          <hr>
          <button class="btn btn-sm btn-primary" onclick="loadAnalytics()">
            <i class="bx bx-refresh me-1"></i>Retry
          </button>
        </div>`;
    }
  });
}

function renderAdvancedAnalytics(d) {
  const content = document.getElementById('analyticsContent');
  let html = '';
  
  // Enhanced Summary Stats Cards - Row 1
  html += '<div class="row mb-4">';
  html += createStatCard('Total Incidents', d.total_incidents || 0, 'primary', 'bx-file');
  html += createStatCard('Resolved', d.resolved_count || 0, 'success', 'bx-check-circle');
  html += createStatCard('Open', d.open_count || 0, 'warning', 'bx-time-five');
  html += createStatCard('Resolution Rate', (d.resolution_rate || 0).toFixed(1) + '%', 'info', 'bx-trending-up');
  html += '</div>';
  
  // Enhanced Summary Stats Cards - Row 2
  html += '<div class="row mb-4">';
  html += createStatCard('Avg Resolution', (d.avg_resolution_time || 0).toFixed(1) + ' days', 'info', 'bx-timer');
  html += createStatCard('Avg Response', (d.avg_response_time || 0).toFixed(1) + ' hrs', 'secondary', 'bx-stopwatch');
  html += createStatCard('Median Response', (d.median_response_time || 0).toFixed(1) + ' hrs', 'secondary', 'bx-time');
  html += createStatCard('Closed', d.closed_count || 0, 'success', 'bx-check');
  html += '</div>';
  
  // Enhanced Summary Stats Cards - Row 3
  html += '<div class="row mb-4">';
  html += createStatCard('New', d.new_count || 0, 'primary', 'bx-plus-circle');
  html += createStatCard('Assigned', d.assigned_count || 0, 'info', 'bx-user-check');
  html += createStatCard('In Progress', d.in_progress_count || 0, 'warning', 'bx-loader-circle');
  html += createStatCard('Rejected', d.rejected_count || 0, 'danger', 'bx-x-circle');
  html += '</div>';
  
  // Priority Breakdown Cards
  html += '<div class="row mb-4">';
  html += '<div class="col-md-3 mb-3"><div class="card stat-card info"><div class="card-body text-center"><h6 class="text-muted mb-2"><i class="bx bx-info-circle me-1"></i>Low Priority</h6><h3 class="mb-0">' + (d.low_priority_count || 0) + '</h3></div></div></div>';
  html += '<div class="col-md-3 mb-3"><div class="card stat-card warning"><div class="card-body text-center"><h6 class="text-muted mb-2"><i class="bx bx-error-circle me-1"></i>Medium Priority</h6><h3 class="mb-0">' + (d.medium_priority_count || 0) + '</h3></div></div></div>';
  html += '<div class="col-md-3 mb-3"><div class="card stat-card danger"><div class="card-body text-center"><h6 class="text-muted mb-2"><i class="bx bx-error me-1"></i>High Priority</h6><h3 class="mb-0">' + (d.high_priority_count || 0) + '</h3></div></div></div>';
  html += '<div class="col-md-3 mb-3"><div class="card stat-card danger"><div class="card-body text-center"><h6 class="text-muted mb-2"><i class="bx bx-error-alt me-1"></i>Critical</h6><h3 class="mb-0">' + (d.critical_priority_count || 0) + '</h3></div></div></div>';
  html += '</div>';
  
  // Charts Row 1: Status & Priority Distribution (ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-6 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bx bx-pie-chart me-1"></i>Status Distribution</h6></div><div class="card-body"><div id="statusChart" class="chart-container"></div></div></div></div>';
  html += '<div class="col-md-6 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bx bx-bar-chart me-1"></i>Priority Distribution</h6></div><div class="card-body"><div id="priorityChart" class="chart-container"></div></div></div></div>';
  html += '</div>';
  
  // Charts Row 2: Category & Department Distribution (ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-6 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bx bx-category me-1"></i>Category Distribution</h6></div><div class="card-body"><div id="categoryChart" class="chart-container"></div></div></div></div>';
  html += '<div class="col-md-6 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="bx bx-buildings me-1"></i>Department Distribution</h6></div><div class="card-body"><div id="departmentChart" class="chart-container"></div></div></div></div>';
  html += '</div>';
  
  // Charts Row 3: Daily & Monthly Trends (ALWAYS SHOW BOTH)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bx bx-line-chart me-1"></i>Daily Trends (Area Chart)</h6></div><div class="card-body"><div id="dailyTrendsChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bx bx-line-chart me-1"></i>Monthly Trends (Area Chart)</h6></div><div class="card-body"><div id="monthlyTrendsChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  // Charts Row 4: Status Over Time & Priority Trends (ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bx bx-trending-up me-1"></i>Status Over Time (Multi-Line Chart)</h6></div><div class="card-body"><div id="statusOverTimeChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="bx bx-trending-up me-1"></i>Priority Trends Over Time (Stacked Area)</h6></div><div class="card-body"><div id="priorityTrendsChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  // Charts Row 5: Resolution Time Distribution (ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-danger text-white"><h6 class="mb-0"><i class="bx bx-time me-1"></i>Resolution Time Distribution</h6></div><div class="card-body"><div id="resolutionTimeChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  // Charts Row 6: Response Time Analysis (NEW - ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12 mb-3"><div class="card analytics-card shadow-sm"><div class="card-header bg-secondary text-white"><h6 class="mb-0"><i class="bx bx-stopwatch me-1"></i>Response Time Analysis</h6></div><div class="card-body"><div id="responseTimeChart" class="chart-container-lg"></div></div></div></div>';
  html += '</div>';
  
  // Tables Row: Top Assignees & Top Reporters (ALWAYS SHOW)
  html += '<div class="row mb-4">';
  html += '<div class="col-md-6 mb-3"><div class="card shadow-sm"><div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bx bx-user-check me-1"></i>Top Assignees</h6></div><div class="card-body"><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>#</th><th>Assignee</th><th class="text-end">Incidents</th></tr></thead><tbody id="topAssigneesTable">';
  if (d.top_assignees && d.top_assignees.length > 0) {
    d.top_assignees.forEach((item, index) => {
      html += `<tr><td>${index + 1}</td><td>${item.user || 'Unassigned'}</td><td class="text-end"><strong>${item.count}</strong></td></tr>`;
    });
  } else {
    html += '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
  }
  html += '</tbody></table></div></div></div></div>';
  
  html += '<div class="col-md-6 mb-3"><div class="card shadow-sm"><div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bx bx-user me-1"></i>Top Reporters</h6></div><div class="card-body"><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>#</th><th>Reporter</th><th class="text-end">Incidents</th></tr></thead><tbody id="topReportersTable">';
  if (d.top_reporters && d.top_reporters.length > 0) {
    d.top_reporters.forEach((item, index) => {
      html += `<tr><td>${index + 1}</td><td>${item.user || 'Unknown'}</td><td class="text-end"><strong>${item.count}</strong></td></tr>`;
    });
  } else {
    html += '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
  }
  html += '</tbody></table></div></div></div></div>';
  html += '</div>';
  
  content.innerHTML = html;
  
  // Render charts after a short delay to ensure DOM is ready and ApexCharts is loaded
  setTimeout(() => {
    console.log('Checking for ApexCharts...');
    console.log('ApexCharts available:', typeof ApexCharts !== 'undefined');
    
    // Check if ApexCharts is available
    if (typeof ApexCharts === 'undefined') {
      console.warn('ApexCharts is not loaded! Waiting...');
      // Try to wait a bit more and load from CDN if needed
      setTimeout(() => {
        if (typeof ApexCharts !== 'undefined') {
          console.log('ApexCharts loaded, rendering charts...');
          renderCharts(d);
        } else {
          console.error('ApexCharts still not available after delay. Loading from CDN...');
          // Try loading ApexCharts from CDN
          const script = document.createElement('script');
          script.src = 'https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js';
          script.onload = function() {
            console.log('ApexCharts loaded from CDN, rendering charts...');
            renderCharts(d);
          };
          script.onerror = function() {
            console.error('Failed to load ApexCharts from CDN');
            const errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-danger';
            errorMsg.innerHTML = '<strong>Error:</strong> Chart library (ApexCharts) could not be loaded. Please refresh the page.';
            content.insertBefore(errorMsg, content.firstChild);
          };
          document.head.appendChild(script);
        }
      }, 500);
    } else {
      console.log('ApexCharts is available, rendering charts...');
      renderCharts(d);
    }
  }, 300);
}

function createStatCard(title, value, color, icon) {
  return `<div class="col-md-3 mb-3">
    <div class="card stat-card ${color}">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">${title}</h6>
            <h3 class="mb-0">${value}</h3>
          </div>
          <div>
            <i class="bx ${icon} bx-lg text-${color}" style="opacity: 0.3;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>`;
}

function renderCharts(d) {
  console.log('=== Starting chart rendering ===');
  console.log('Chart data:', d);
  console.log('ApexCharts available:', typeof ApexCharts !== 'undefined');
  
  // Check if ApexCharts is available
  if (typeof ApexCharts === 'undefined') {
    console.error('ApexCharts is not defined! Cannot render charts.');
    const content = document.getElementById('analyticsContent');
    if (content) {
      const errorMsg = document.createElement('div');
      errorMsg.className = 'alert alert-danger';
      errorMsg.innerHTML = '<strong>Error:</strong> Chart library (ApexCharts) is not loaded. Please refresh the page.';
      content.insertBefore(errorMsg, content.firstChild);
    }
    return;
  }
  
  console.log('ApexCharts is available, proceeding with chart rendering...');
  
  // Status Distribution Pie Chart (ALWAYS RENDER)
  const statusChartEl = document.getElementById('statusChart');
  if (statusChartEl) {
    try {
      const statusData = d.status_distribution || {};
      const seriesData = Object.values(statusData).map(v => Number(v) || 0);
      const labels = Object.keys(statusData).length > 0 ? Object.keys(statusData) : ['No Data'];
      const total = seriesData.reduce((a, b) => a + b, 0);
      
      if (total > 0) {
        const statusChart = new ApexCharts(statusChartEl, {
          chart: { type: 'pie', height: 300, animations: { enabled: true } },
          series: seriesData,
          labels: labels,
          colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d', '#fd7e14', '#20c997'],
          legend: { position: 'bottom', fontSize: '12px' },
          tooltip: { enabled: true, y: { formatter: (val) => val + ' incidents' } },
          dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' }
        });
        statusChart.render();
        chartInstances.push(statusChart);
      } else {
        statusChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No status data available</div>';
      }
    } catch (error) {
      console.error('Error rendering status chart:', error);
      statusChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Priority Distribution Bar Chart (ALWAYS RENDER)
  const priorityChartEl = document.getElementById('priorityChart');
  if (priorityChartEl) {
    try {
      const priorityData = d.priority_distribution || {};
      const categories = Object.keys(priorityData).length > 0 ? Object.keys(priorityData) : ['Low', 'Medium', 'High', 'Critical'];
      const values = categories.map(cat => Number(priorityData[cat]) || 0);
      const total = values.reduce((a, b) => a + b, 0);
      
      if (total > 0) {
        const priorityChart = new ApexCharts(priorityChartEl, {
          chart: { type: 'bar', height: 300, animations: { enabled: true } },
          series: [{ name: 'Incidents', data: values }],
          xaxis: { categories: categories },
          colors: ['#0d6efd', '#0dcaf0', '#ffc107', '#dc3545'],
          plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '60%' } },
          dataLabels: { enabled: true },
          tooltip: { y: { formatter: (val) => val + ' incidents' } }
        });
        priorityChart.render();
        chartInstances.push(priorityChart);
      } else {
        priorityChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No priority data available</div>';
      }
    } catch (error) {
      console.error('Error rendering priority chart:', error);
      priorityChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Category Distribution Donut Chart (ALWAYS RENDER)
  const categoryChartEl = document.getElementById('categoryChart');
  if (categoryChartEl) {
    try {
      const categoryData = d.category_distribution || {};
      const seriesData = Object.values(categoryData).map(v => Number(v) || 0);
      const labels = Object.keys(categoryData).length > 0 
        ? Object.keys(categoryData).map(c => c || 'Uncategorized')
        : ['No Data'];
      const total = seriesData.reduce((a, b) => a + b, 0);
      
      if (total > 0) {
        const categoryChart = new ApexCharts(categoryChartEl, {
          chart: { type: 'donut', height: 300, animations: { enabled: true } },
          series: seriesData,
          labels: labels,
          colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d', '#fd7e14', '#20c997'],
          legend: { position: 'bottom', fontSize: '12px' },
          dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
          tooltip: { y: { formatter: (val) => val + ' incidents' } }
        });
        categoryChart.render();
        chartInstances.push(categoryChart);
      } else {
        categoryChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No category data available</div>';
      }
    } catch (error) {
      console.error('Error rendering category chart:', error);
      categoryChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Department Distribution (ALWAYS RENDER)
  const deptChartEl = document.getElementById('departmentChart');
  if (deptChartEl) {
    try {
      const deptData = d.department_distribution || {};
      const categories = Object.keys(deptData).length > 0 ? Object.keys(deptData) : ['No Departments'];
      const values = categories.map(dept => Number(deptData[dept]) || 0);
      const total = values.reduce((a, b) => a + b, 0);
      
      if (total > 0) {
        const deptChart = new ApexCharts(deptChartEl, {
          chart: { type: 'bar', height: 300, animations: { enabled: true } },
          series: [{ name: 'Incidents', data: values }],
          xaxis: { categories: categories, labels: { rotate: -45, style: { fontSize: '11px' } } },
          colors: ['#ffc107'],
          plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '60%' } },
          dataLabels: { enabled: true },
          tooltip: { y: { formatter: (val) => val + ' incidents' } }
        });
        deptChart.render();
        chartInstances.push(deptChart);
      } else {
        deptChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No department data available</div>';
      }
    } catch (error) {
      console.error('Error rendering department chart:', error);
      deptChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Daily Trends Area Chart (ALWAYS RENDER)
  const dailyChartEl = document.getElementById('dailyTrendsChart');
  if (dailyChartEl) {
    try {
      const dailyTrends = d.daily_trends || {};
      const dates = Object.keys(dailyTrends).length > 0 ? Object.keys(dailyTrends).sort() : [];
      const labels = dates.map(date => {
        try {
          const dateObj = new Date(date);
          return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        } catch {
          return date;
        }
      });
      const dailyData = dates.map(date => Number(dailyTrends[date]) || 0);
      const total = dailyData.reduce((a, b) => a + b, 0);
      
      if (total > 0 || dates.length > 0) {
        const dailyChart = new ApexCharts(dailyChartEl, {
          chart: { type: 'area', height: 400, zoom: { enabled: true }, animations: { enabled: true } },
          series: [{ name: 'Daily Incidents', data: dailyData }],
          xaxis: { categories: labels.length > 0 ? labels : ['No Data'], labels: { rotate: -45, style: { fontSize: '11px' } } },
          colors: ['#0d6efd'],
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3, stops: [0, 90, 100] } },
          stroke: { curve: 'smooth', width: 3 },
          dataLabels: { enabled: false },
          tooltip: { y: { formatter: (val) => val + ' incidents' } },
          grid: { borderColor: '#e7e7e7', strokeDashArray: 3 }
        });
        dailyChart.render();
        chartInstances.push(dailyChart);
      } else {
        dailyChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No daily trends data available</div>';
      }
    } catch (error) {
      console.error('Error rendering daily trends chart:', error);
      dailyChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Monthly Trends Area Chart (ALWAYS RENDER)
  const monthlyChartEl = document.getElementById('monthlyTrendsChart');
  if (monthlyChartEl) {
    try {
      const monthlyTrends = d.monthly_trends || {};
      const months = Object.keys(monthlyTrends).length > 0 ? Object.keys(monthlyTrends).sort() : [];
      const labels = months.map(month => {
        try {
          const dateObj = new Date(month + '-01');
          return dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
        } catch {
          return month;
        }
      });
      const monthlyData = months.map(month => Number(monthlyTrends[month]) || 0);
      const total = monthlyData.reduce((a, b) => a + b, 0);
      
      if (total > 0 || months.length > 0) {
        const monthlyChart = new ApexCharts(monthlyChartEl, {
          chart: { type: 'area', height: 400, zoom: { enabled: true }, animations: { enabled: true } },
          series: [{ name: 'Monthly Incidents', data: monthlyData }],
          xaxis: { categories: labels.length > 0 ? labels : ['No Data'] },
          colors: ['#0dcaf0'],
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3, stops: [0, 90, 100] } },
          stroke: { curve: 'smooth', width: 3 },
          dataLabels: { enabled: false },
          tooltip: { y: { formatter: (val) => val + ' incidents' } },
          grid: { borderColor: '#e7e7e7', strokeDashArray: 3 }
        });
        monthlyChart.render();
        chartInstances.push(monthlyChart);
      } else {
        monthlyChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No monthly trends data available</div>';
      }
    } catch (error) {
      console.error('Error rendering monthly trends chart:', error);
      monthlyChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Status Over Time Multi-Line Chart (ALWAYS RENDER)
  const statusTimeChartEl = document.getElementById('statusOverTimeChart');
  if (statusTimeChartEl) {
    try {
      const statusOverTime = d.status_over_time || {};
      const statuses = Object.keys(statusOverTime).length > 0 ? Object.keys(statusOverTime) : [];
      const months = new Set();
      statuses.forEach(status => {
        if (statusOverTime[status]) {
          Object.keys(statusOverTime[status]).forEach(month => months.add(month));
        }
      });
      const sortedMonths = Array.from(months).sort();
      const labels = sortedMonths.map(month => {
        try {
          const dateObj = new Date(month + '-01');
          return dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
        } catch {
          return month;
        }
      });
      
      const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d', '#fd7e14', '#20c997'];
      const series = statuses.length > 0 ? statuses.map((status, index) => {
        const seriesData = sortedMonths.map(month => Number(statusOverTime[status]?.[month]) || 0);
        return {
          name: status,
          data: seriesData,
          color: colors[index % colors.length]
        };
      }) : [{ name: 'No Data', data: [], color: '#6c757d' }];
      
      const hasData = series.some(s => s.data.reduce((a, b) => a + b, 0) > 0);
      if (hasData && statuses.length > 0) {
        const statusTimeChart = new ApexCharts(statusTimeChartEl, {
          chart: { type: 'line', height: 400, zoom: { enabled: true }, animations: { enabled: true } },
          series: series,
          xaxis: { categories: labels.length > 0 ? labels : ['No Data'] },
          stroke: { curve: 'smooth', width: 3 },
          legend: { position: 'bottom', fontSize: '12px' },
          dataLabels: { enabled: false },
          tooltip: { y: { formatter: (val) => val + ' incidents' } },
          grid: { borderColor: '#e7e7e7', strokeDashArray: 3 }
        });
        statusTimeChart.render();
        chartInstances.push(statusTimeChart);
      } else {
        statusTimeChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No status over time data available</div>';
      }
    } catch (error) {
      console.error('Error rendering status over time chart:', error);
      statusTimeChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Priority Trends Stacked Area Chart (ALWAYS RENDER)
  const priorityTimeChartEl = document.getElementById('priorityTrendsChart');
  if (priorityTimeChartEl) {
    try {
      const priorityTrends = d.priority_trends || {};
      const priorities = Object.keys(priorityTrends).length > 0 ? Object.keys(priorityTrends) : ['Low', 'Medium', 'High', 'Critical'];
      const months = new Set();
      priorities.forEach(priority => {
        if (priorityTrends[priority]) {
          Object.keys(priorityTrends[priority]).forEach(month => months.add(month));
        }
      });
      const sortedMonths = Array.from(months).sort();
      const labels = sortedMonths.map(month => {
        try {
          const dateObj = new Date(month + '-01');
          return dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
        } catch {
          return month;
        }
      });
      
      const colors = ['#0dcaf0', '#ffc107', '#fd7e14', '#dc3545'];
      const series = priorities.map((priority, index) => {
        const seriesData = sortedMonths.map(month => Number(priorityTrends[priority]?.[month]) || 0);
        return {
          name: priority,
          data: seriesData,
          color: colors[index % colors.length]
        };
      });
      
      const hasData = series.some(s => s.data.reduce((a, b) => a + b, 0) > 0);
      if (hasData && priorities.length > 0) {
        const priorityTimeChart = new ApexCharts(priorityTimeChartEl, {
          chart: { type: 'area', height: 400, zoom: { enabled: true }, stacked: true, animations: { enabled: true } },
          series: series,
          xaxis: { categories: labels.length > 0 ? labels : ['No Data'] },
          stroke: { curve: 'smooth', width: 2 },
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.3 } },
          legend: { position: 'bottom', fontSize: '12px' },
          dataLabels: { enabled: false },
          tooltip: { y: { formatter: (val) => val + ' incidents' } },
          grid: { borderColor: '#e7e7e7', strokeDashArray: 3 }
        });
        priorityTimeChart.render();
        chartInstances.push(priorityTimeChart);
      } else {
        priorityTimeChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No priority trends data available</div>';
      }
    } catch (error) {
      console.error('Error rendering priority trends chart:', error);
      priorityTimeChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Resolution Time Distribution (ALWAYS RENDER)
  const resolutionChartEl = document.getElementById('resolutionTimeChart');
  if (resolutionChartEl) {
    try {
      const resolutionBuckets = d.resolution_time_buckets || {};
      const categories = Object.keys(resolutionBuckets).length > 0 ? Object.keys(resolutionBuckets) : ['< 1 day', '1-3 days', '3-7 days', '1-2 weeks', '2-4 weeks', '> 4 weeks'];
      const values = categories.map(cat => Number(resolutionBuckets[cat]) || 0);
      const total = values.reduce((a, b) => a + b, 0);
      
      if (total > 0) {
        const resolutionChart = new ApexCharts(resolutionChartEl, {
          chart: { type: 'bar', height: 400, animations: { enabled: true } },
          series: [{ name: 'Incidents', data: values }],
          xaxis: { categories: categories, labels: { rotate: -45, style: { fontSize: '11px' } } },
          colors: ['#198754', '#20c997', '#0dcaf0', '#ffc107', '#fd7e14', '#dc3545'],
          plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '60%', distributed: true } },
          dataLabels: { enabled: true },
          tooltip: { y: { formatter: (val) => val + ' incidents' } }
        });
        resolutionChart.render();
        chartInstances.push(resolutionChart);
      } else {
        resolutionChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No resolution time data available</div>';
      }
    } catch (error) {
      console.error('Error rendering resolution time chart:', error);
      resolutionChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  // Response Time Analysis Chart (NEW - ALWAYS RENDER)
  const responseTimeChartEl = document.getElementById('responseTimeChart');
  if (responseTimeChartEl) {
    try {
      const avgResponse = d.avg_response_time || 0;
      const medianResponse = d.median_response_time || 0;
      
      if (avgResponse > 0 || medianResponse > 0) {
        const responseTimeChart = new ApexCharts(responseTimeChartEl, {
          chart: { type: 'bar', height: 400, animations: { enabled: true } },
          series: [
            { name: 'Average Response Time (hours)', data: [avgResponse] },
            { name: 'Median Response Time (hours)', data: [medianResponse] }
          ],
          xaxis: { categories: ['Response Time Metrics'] },
          colors: ['#0dcaf0', '#6c757d'],
          plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '50%' } },
          dataLabels: { enabled: true, formatter: (val) => val.toFixed(2) + ' hrs' },
          tooltip: { y: { formatter: (val) => val.toFixed(2) + ' hours' } },
          legend: { position: 'bottom' }
        });
        responseTimeChart.render();
        chartInstances.push(responseTimeChart);
      } else {
        responseTimeChartEl.innerHTML = '<div class="text-center text-muted p-4"><i class="bx bx-info-circle"></i><br>No response time data available</div>';
      }
    } catch (error) {
      console.error('Error rendering response time chart:', error);
      responseTimeChartEl.innerHTML = '<div class="text-center text-danger p-4">Error rendering chart</div>';
    }
  }
  
  console.log('=== Chart rendering completed ===');
  console.log('Total chart instances created:', chartInstances.length);
  console.log('Chart instances:', chartInstances);
  
  // Show success message if charts rendered
  if (chartInstances.length > 0) {
    console.log('✅ All charts rendered successfully!');
  } else {
    console.warn('⚠️ No charts were rendered. Check data availability.');
  }
}

function exportAnalytics() {
  const dateFrom = document.getElementById('analyticsDateFrom').value;
  const dateTo = document.getElementById('analyticsDateTo').value;
  window.open(`{{ route("modules.incidents.export") }}?date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
}

// Load analytics when modal is shown
document.addEventListener('DOMContentLoaded', function() {
  const analyticsModal = document.getElementById('analyticsModal');
  if (analyticsModal) {
    console.log('Analytics modal found, setting up event listeners...');
    
    analyticsModal.addEventListener('shown.bs.modal', function() {
      console.log('Analytics modal opened, loading analytics...');
      loadAnalytics();
    });
    
    analyticsModal.addEventListener('hidden.bs.modal', function() {
      console.log('Analytics modal closed, destroying charts...');
      destroyCharts();
    });
  } else {
    console.warn('Analytics modal element not found!');
  }
  
  // Also try to set up if modal is already in DOM
  setTimeout(function() {
    const modal = document.getElementById('analyticsModal');
    if (modal && !modal.hasAttribute('data-listener-set')) {
      modal.setAttribute('data-listener-set', 'true');
      modal.addEventListener('shown.bs.modal', function() {
        console.log('Analytics modal opened (fallback), loading analytics...');
        loadAnalytics();
      });
    }
  }, 1000);
});
</script>
