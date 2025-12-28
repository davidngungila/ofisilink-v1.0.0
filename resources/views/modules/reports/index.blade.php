@extends('layouts.app')

@section('title', 'Reporting & Analytics')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">Reporting & Analytics</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Reports</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6 m-b20">
            <div class="widget-box">
                <div class="wc-title"><h4>Finance Overview</h4></div>
                <div class="widget-inner">
                    <canvas id="financeChart" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 m-b20">
            <div class="widget-box">
                <div class="wc-title"><h4>HR Overview</h4></div>
                <div class="widget-inner">
                    <canvas id="hrChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
    (function(){
        function initCharts() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded, attempting to load from CDN...');
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
                script.onload = function() {
                    console.log('Chart.js loaded from CDN, initializing charts...');
                    initCharts();
                };
                script.onerror = function() {
                    console.error('Failed to load Chart.js from CDN');
                };
                document.head.appendChild(script);
                return;
            }
            
            const financeCtx = document.getElementById('financeChart');
            const hrCtx = document.getElementById('hrChart');
            
            if (financeCtx) {
                new Chart(financeCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan','Feb','Mar','Apr','May'],
                        datasets: [
                            {label: 'Expenses', data: [12,19,7,9,14], backgroundColor: '#06b6d4'},
                            {label: 'Imprest', data: [8,11,5,6,9], backgroundColor: '#22c55e'}
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                console.log('✅ Finance chart rendered');
            }
            
            if (hrCtx) {
                new Chart(hrCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan','Feb','Mar','Apr','May'],
                        datasets: [{
                            label: 'Leaves',
                            data: [5,7,6,8,9],
                            borderColor: '#f59e0b',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                console.log('✅ HR chart rendered');
            }
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    })();
</script>
@endpush



