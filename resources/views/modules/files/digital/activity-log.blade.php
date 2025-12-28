@extends('layouts.app')

@section('title', 'Activity Log')

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
                                <i class="bx bx-history me-2"></i>Activity Log
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Complete audit trail of all file and folder activities
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

    <!-- Activity Log -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">All Activities</h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="activity-search-input" placeholder="Search activities...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary active" id="filter-all">All</button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-upload">Uploads</button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-download">Downloads</button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-delete">Deletions</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="activities-table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>File/Folder</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                <tr class="activity-row" data-action-type="{{ strtolower($activity->activity_type ?? '') }}" data-user="{{ strtolower($activity->user->name ?? 'system') }}" data-item="{{ strtolower(($activity->file->original_name ?? ($activity->details['folder_name'] ?? ''))) }}">
                                    <td>
                                        @php
                                            $activityType = $activity->activity_type ?? 'unknown';
                                            $badgeClass = 'info';
                                            if (str_contains($activityType, 'upload')) $badgeClass = 'success';
                                            elseif (str_contains($activityType, 'delete')) $badgeClass = 'danger';
                                            elseif (str_contains($activityType, 'download')) $badgeClass = 'primary';
                                            elseif (str_contains($activityType, 'assign')) $badgeClass = 'warning';
                                        @endphp
                                        <span class="badge bg-label-{{ $badgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $activityType)) }}
                                        </span>
                                    </td>
                                    <td>{{ $activity->user->name ?? 'System' }}</td>
                                    <td>
                                        @if($activity->file)
                                            <i class="bx bx-file me-1 text-success"></i>
                                            <a href="{{ route('modules.files.digital.folder.detail', $activity->file->folder_id) }}" class="text-decoration-none">
                                                {{ $activity->file->original_name }}
                                            </a>
                                        @elseif(isset($activity->details['folder_name']))
                                            <i class="bx bx-folder me-1 text-primary"></i>
                                            <span>{{ $activity->details['folder_name'] }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->details && is_array($activity->details))
                                            @php
                                                $detailParts = [];
                                                if (isset($activity->details['original_name'])) $detailParts[] = 'File: ' . $activity->details['original_name'];
                                                if (isset($activity->details['file_size'])) $detailParts[] = 'Size: ' . number_format($activity->details['file_size'] / 1024 / 1024, 2) . ' MB';
                                                if (isset($activity->details['folder_name'])) $detailParts[] = 'Folder: ' . $activity->details['folder_name'];
                                                if (isset($activity->details['user_id'])) $detailParts[] = 'User ID: ' . $activity->details['user_id'];
                                            @endphp
                                            <small class="text-muted">{{ implode(' | ', $detailParts) ?: '-' }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $activity->activity_date ? \Carbon\Carbon::parse($activity->activity_date)->format('M d, Y H:i') : $activity->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    
    // Live search
    $('#activity-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            $('.activity-row').each(function() {
                const user = $(this).data('user') || '';
                const item = $(this).data('item') || '';
                const actionType = $(this).data('action-type') || '';
                
                if (user.includes(searchTerm) || item.includes(searchTerm) || actionType.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // Filter buttons
    $('#filter-all').click(function() {
        $('.activity-row').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-upload').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="upload"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-download').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="download"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-delete').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="delete"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
@endpush
</div>
@endsection

