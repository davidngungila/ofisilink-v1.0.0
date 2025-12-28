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
                                Complete audit trail of all physical file and rack activities
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
                                <button class="btn btn-sm btn-outline-primary" id="filter-request">Requests</button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-return">Returns</button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-create">Create</button>
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
                                    <th>File/Rack</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                @php
                                    $file = null;
                                    if ($activity->details && isset($activity->details['file_id'])) {
                                        $file = \App\Models\RackFile::find($activity->details['file_id']);
                                    }
                                    $fileName = $file ? $file->file_name : (isset($activity->details['file_name']) ? $activity->details['file_name'] : '');
                                @endphp
                                <tr class="activity-row" data-action-type="{{ strtolower($activity->activity_type ?? '') }}" data-user="{{ strtolower($activity->user->name ?? 'system') }}" data-item="{{ strtolower($fileName) }}">
                                    <td>
                                        @php
                                            $activityType = $activity->activity_type ?? 'unknown';
                                            $badgeClass = 'info';
                                            if (str_contains($activityType, 'request')) $badgeClass = 'warning';
                                            elseif (str_contains($activityType, 'return')) $badgeClass = 'success';
                                            elseif (str_contains($activityType, 'create')) $badgeClass = 'primary';
                                            elseif (str_contains($activityType, 'delete')) $badgeClass = 'danger';
                                        @endphp
                                        <span class="badge bg-label-{{ $badgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $activityType)) }}
                                        </span>
                                    </td>
                                    <td>{{ $activity->user->name ?? 'System' }}</td>
                                    <td>
                                        @if($file)
                                            <i class="bx bx-file me-1 text-success"></i>
                                            <a href="{{ route('modules.files.physical.rack.detail', $file->folder_id) }}" class="text-decoration-none">
                                                {{ $file->file_name }}
                                            </a>
                                        @elseif(isset($activity->details['rack_name']))
                                            <i class="bx bx-archive me-1 text-primary"></i>
                                            <span>{{ $activity->details['rack_name'] }}</span>
                                        @elseif($activity->folder)
                                            <i class="bx bx-archive me-1 text-primary"></i>
                                            <a href="{{ route('modules.files.physical.rack.detail', $activity->folder_id) }}" class="text-decoration-none">
                                                {{ $activity->folder->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->details && is_array($activity->details))
                                            @php
                                                $detailParts = [];
                                                if (isset($activity->details['file_name'])) $detailParts[] = 'File: ' . $activity->details['file_name'];
                                                if (isset($activity->details['rack_name'])) $detailParts[] = 'Rack: ' . $activity->details['rack_name'];
                                                if (isset($activity->details['purpose'])) $detailParts[] = 'Purpose: ' . $activity->details['purpose'];
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
    
    $('#filter-request').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="request"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-return').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="return"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-create').click(function() {
        $('.activity-row').hide();
        $('.activity-row[data-action-type*="create"]').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
@endpush
@endsection

