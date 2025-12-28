@extends('layouts.app')

@section('title', 'Assessment Analytics - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-bar-chart-alt-2 me-2"></i>Assessment Analytics</h4>
                            <p class="mb-0 text-muted">Comprehensive analytics and insights</p>
                        </div>
                        <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Analytics Dashboard</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text">Year</span>
                            <input type="number" class="form-control" id="analytics-year" value="{{ date('Y') }}" min="2000" max="2100">
                            <button class="btn btn-primary" id="btn-load-analytics">
                                <i class="bx bx-refresh"></i> Load
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="analytics-content">
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-bar-chart-alt-2" style="font-size: 3rem;"></i>
                            <p class="mt-2">Click "Load" to view comprehensive analytics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$('#btn-load-analytics').on('click', function() {
    const year = $('#analytics-year').val();
    const content = $('#analytics-content');
    content.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading analytics...</p></div>');
    
    fetch('{{ route("assessments.analytics.data") }}?year=' + year)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderAnalyticsCharts(data);
            } else {
                content.html('<div class="alert alert-danger">Failed to load analytics</div>');
            }
        })
        .catch(err => {
            content.html('<div class="alert alert-danger">Error: ' + err.message + '</div>');
        });
});

function renderAnalyticsCharts(data) {
    let html = '<div class="row mb-4">';
    html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Assessment Status</h6></div><div class="card-body"><canvas id="statusChart"></canvas></div></div></div>';
    html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Report Status</h6></div><div class="card-body"><canvas id="reportStatusChart"></canvas></div></div></div>';
    html += '<div class="col-md-12 mb-4"><div class="card"><div class="card-header"><h6>Monthly Trend (' + data.year + ')</h6></div><div class="card-body"><canvas id="trendChart"></canvas></div></div></div>';
    html += '</div>';
    $('#analytics-content').html(html);
    
    setTimeout(() => {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending HOD', 'Rejected'],
                datasets: [{ data: [data.status_distribution.approved, data.status_distribution.pending_hod, data.status_distribution.rejected], backgroundColor: ['#28a745', '#ffc107', '#dc3545'] }]
            }
        });
        new Chart(document.getElementById('reportStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{ data: [data.report_status_distribution.approved, data.report_status_distribution.pending_approval, data.report_status_distribution.rejected], backgroundColor: ['#28a745', '#ffc107', '#dc3545'] }]
            }
        });
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const assessmentData = [], reportData = [];
        for (let i = 1; i <= 12; i++) {
            assessmentData.push(data.monthly_trend[i].assessments);
            reportData.push(data.monthly_trend[i].reports);
        }
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Assessments',
                    data: assessmentData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Progress Reports',
                    data: reportData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }, 100);
}
</script>
@endpush

