@extends('layouts.app')

@section('title', 'Physical Files Analytics')

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
                                <i class="bx bx-bar-chart me-2"></i>Physical Files Analytics
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive statistics and insights about your physical file management system
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Racks</h6>
                    <h3 class="text-primary">{{ $stats['total_folders'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Files</h6>
                    <h3 class="text-success">{{ $stats['total_files'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Issued Files</h6>
                    <h3 class="text-warning">{{ $stats['issued_files'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending Requests</h6>
                    <h3 class="text-danger">{{ $stats['pending_requests'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- File Type Distribution -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">File Type Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>File Type</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fileTypes as $type)
                                <tr>
                                    <td><strong>{{ strtoupper($type->file_type ?? 'Unknown') }}</strong></td>
                                    <td>{{ $type->count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statusStats as $stat)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-{{ $stat->status === 'issued' ? 'warning' : ($stat->status === 'archived' ? 'secondary' : 'success') }}">
                                            {{ ucfirst($stat->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $stat->count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Over Time -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Activity Over Time (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const activityData = @json($activityData);
    const ctx = document.getElementById('activityChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: activityData.map(item => item.date),
            datasets: [{
                label: 'Activities',
                data: activityData.map(item => item.count),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
@endsection


