@extends('layouts.app')

@section('title', 'File Analytics')

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
                                <i class="bx bx-bar-chart-alt-2 me-2"></i>Advanced File Analytics
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive statistics and insights about your file management system
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Statistics Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary border-top border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Folders</h6>
                            <h2 class="text-primary mb-0">{{ number_format($stats['total_folders'] ?? 0) }}</h2>
                            <small class="text-muted">Active folders</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="bx bx-folder fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success border-top border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Files</h6>
                            <h2 class="text-success mb-0">{{ number_format($stats['total_files'] ?? 0) }}</h2>
                            <small class="text-muted">All files</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-success rounded">
                                <i class="bx bx-file fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info border-top border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Storage Used</h6>
                            <h2 class="text-info mb-0">{{ number_format(($stats['total_storage'] ?? 0) / 1024 / 1024, 2) }} <small class="fs-6">MB</small></h2>
                            <small class="text-muted">{{ number_format(($stats['total_storage'] ?? 0) / 1024 / 1024 / 1024, 2) }} GB</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-info rounded">
                                <i class="bx bx-hdd fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning border-top border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Downloads</h6>
                            <h2 class="text-warning mb-0">{{ number_format($stats['total_downloads'] ?? 0) }}</h2>
                            <small class="text-muted">All time</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="bx bx-download fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Active Assignments</h6>
                    <h3 class="text-primary">{{ number_format($assignmentStats->total_assignments ?? 0) }}</h3>
                    <small class="text-muted">{{ $assignmentStats->users_assigned ?? 0 }} users, {{ $assignmentStats->files_assigned ?? 0 }} files</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Access Requests</h6>
                    <h3 class="text-info">{{ number_format($accessRequestStats->sum('count') ?? 0) }}</h3>
                    <small class="text-muted">
                        @if($accessRequestStats->where('status', 'pending')->first())
                            {{ $accessRequestStats->where('status', 'pending')->first()->count }} pending
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Top Uploader</h6>
                    <h3 class="text-success">{{ $topUploaders->first()->name ?? 'N/A' }}</h3>
                    <small class="text-muted">{{ $topUploaders->first()->upload_count ?? 0 }} files uploaded</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Most Downloaded</h6>
                    <h3 class="text-warning">{{ $topDownloaded->first()->download_count ?? 0 }}</h3>
                    <small class="text-muted">downloads for top file</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 1 -->
    <div class="row mb-4">
        <!-- File Type Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-pie-chart-alt-2 me-2"></i>File Type Distribution</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Count</th>
                                        <th>Size</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalFiles = $fileTypes->sum('count');
                                        $totalSize = $fileTypes->sum('total_size');
                                    @endphp
                                    @foreach($fileTypes as $type)
                                    <tr>
                                        <td><strong>{{ $type->file_type }}</strong></td>
                                        <td>{{ number_format($type->count) }}</td>
                                        <td>{{ number_format($type->total_size / 1024 / 1024, 2) }} MB</td>
                                        <td>{{ $totalFiles > 0 ? number_format(($type->count / $totalFiles) * 100, 1) : 0 }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Level Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-shield me-2"></i>Access Level Distribution</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Access Level</th>
                                        <th>Files</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessLevelStats as $stat)
                                    <tr>
                                        <td>
                                            <span class="badge bg-label-{{ $stat->access_level === 'public' ? 'success' : ($stat->access_level === 'department' ? 'info' : 'warning') }}">
                                                {{ ucfirst($stat->access_level) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($stat->count) }}</td>
                                        <td>{{ number_format($stat->total_size / 1024 / 1024, 2) }} MB</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 2 -->
    <div class="row mb-4">
        <!-- Activity Over Time -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i>Activity Over Time (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Activities</th>
                                    <th>Uploads</th>
                                    <th>Downloads</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activityData as $activity)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($activity->date)->format('M d, Y') }}</td>
                                    <td>{{ number_format($activity->count) }}</td>
                                    <td>{{ number_format($activity->uploads) }}</td>
                                    <td>{{ number_format($activity->downloads) }}</td>
                                    <td>{{ number_format($activity->views) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Type Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Activity Types</h5>
                </div>
                <div class="card-body">
                    <div>
                        @foreach($activityTypes as $activity)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small">{{ ucwords(str_replace('_', ' ', $activity->activity_type)) }}</span>
                            <strong>{{ number_format($activity->count) }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 3 -->
    <div class="row mb-4">
        <!-- Storage Growth -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Storage Growth (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Files</th>
                                    <th>Storage (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($storageGrowth as $growth)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $growth->month)->format('M Y') }}</td>
                                    <td>{{ number_format($growth->file_count) }}</td>
                                    <td>{{ number_format($growth->total_size / 1024 / 1024, 2) }} MB</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-buildings me-2"></i>Department Statistics</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Files</th>
                                        <th>Folders</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departmentStats->take(5) as $dept)
                                    <tr>
                                        <td>{{ $dept->department_name ?? 'Unassigned' }}</td>
                                        <td>{{ number_format($dept->file_count) }}</td>
                                        <td>{{ number_format($dept->folder_count) }}</td>
                                        <td>{{ number_format($dept->total_size / 1024 / 1024, 2) }} MB</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 4 -->
    <div class="row mb-4">
        <!-- Confidentiality Level -->
        @if($confidentialityStats->count() > 0)
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-lock me-2"></i>Confidentiality Level Distribution</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Files</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($confidentialityStats as $stat)
                                    <tr>
                                        <td><strong>{{ ucfirst($stat->confidential_level) }}</strong></td>
                                        <td>{{ number_format($stat->count) }}</td>
                                        <td>{{ number_format($stat->total_size / 1024 / 1024, 2) }} MB</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Average File Size by Type -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-data me-2"></i>Average File Size by Type</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Avg Size</th>
                                        <th>Min</th>
                                        <th>Max</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($avgFileSizeByType as $type)
                                    <tr>
                                        <td><strong>{{ $type->file_type }}</strong></td>
                                        <td>{{ number_format($type->avg_size / 1024, 2) }} KB</td>
                                        <td>{{ number_format($type->min_size / 1024, 2) }} KB</td>
                                        <td>{{ number_format($type->max_size / 1024 / 1024, 2) }} MB</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Uploaders and Downloaded Files -->
    <div class="row mb-4">
        <!-- Top Uploaders -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>Top Uploaders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Files</th>
                                    <th>Total Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUploaders as $index => $uploader)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-initial bg-primary rounded">
                                                    {{ substr($uploader->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <span>{{ $uploader->name }}</span>
                                        </div>
                                    </td>
                                    <td><strong>{{ number_format($uploader->upload_count) }}</strong></td>
                                    <td>{{ number_format($uploader->total_size / 1024 / 1024, 2) }} MB</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Downloaded Files -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-download me-2"></i>Most Downloaded Files</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Downloads</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topDownloaded as $index => $file)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-success">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-file me-2 text-primary"></i>
                                            <span class="text-truncate" style="max-width: 200px;" title="{{ $file->original_name }}">
                                                {{ Str::limit($file->original_name, 30) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td><strong>{{ number_format($file->download_count) }}</strong></td>
                                    <td>{{ number_format($file->file_size / 1024 / 1024, 2) }} MB</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Uploads and Folder Statistics -->
    <div class="row mb-4">
        <!-- Recent Uploads -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-time me-2"></i>Recent Uploads (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Uploader</th>
                                    <th>Folder</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUploads as $file)
                                <tr>
                                    <td>
                                        <i class="bx bx-file me-1"></i>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $file->original_name }}">
                                            {{ Str::limit($file->original_name, 20) }}
                                        </span>
                                    </td>
                                    <td>{{ $file->uploader->name ?? 'N/A' }}</td>
                                    <td>{{ $file->folder->name ?? 'N/A' }}</td>
                                    <td>{{ $file->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent uploads</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Folders by Size -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-folder me-2"></i>Top Folders by Size</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Folder</th>
                                    <th>Files</th>
                                    <th>Size</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($folderStats->take(10) as $folder)
                                <tr>
                                    <td>
                                        <i class="bx bx-folder me-1 text-primary"></i>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $folder->name }}">
                                            {{ Str::limit($folder->name, 20) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($folder->files_count) }}</td>
                                    <td>{{ number_format($folder->total_size / 1024 / 1024, 2) }} MB</td>
                                    <td>{{ $folder->updated_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Access Request Statistics -->
    @if($accessRequestStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-key me-2"></i>Access Request Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($accessRequestStats as $stat)
                        <div class="col-md-3 mb-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">{{ ucfirst($stat->status) }}</h6>
                                    <h3 class="text-{{ $stat->status === 'approved' ? 'success' : ($stat->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ number_format($stat->count) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// Analytics page - charts removed, only data tables remain
</script>
@endpush
@endsection
