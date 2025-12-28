@extends('layouts.app')

@section('title', 'Recent System Errors')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="bx bx-error-circle text-danger"></i> Recent System Errors
                    </h4>
                    <p class="text-muted mb-0">Monitor and analyze system errors and exceptions</p>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="refreshErrors()" title="Refresh">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                    <button class="btn btn-sm btn-outline-info me-2" onclick="downloadLogs()" title="Download Log File">
                        <i class="bx bx-download"></i> Download Logs
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearLogs()" title="Clear All Logs">
                        <i class="bx bx-trash"></i> Clear Logs
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="errorStats">
                <div class="col-md-3">
                    <div class="card border-left-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Errors</h6>
                                    <h3 class="mb-0" id="totalErrors">-</h3>
                                </div>
                                <div class="text-danger">
                                    <i class="bx bx-error-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Today</h6>
                                    <h3 class="mb-0" id="errorsToday">-</h3>
                                </div>
                                <div class="text-warning">
                                    <i class="bx bx-calendar fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">This Week</h6>
                                    <h3 class="mb-0" id="errorsWeek">-</h3>
                                </div>
                                <div class="text-info">
                                    <i class="bx bx-calendar-week fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">This Month</h6>
                                    <h3 class="mb-0" id="errorsMonth">-</h3>
                                </div>
                                <div class="text-primary">
                                    <i class="bx bx-calendar-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Error Level</label>
                            <select class="form-select" id="errorLevel" onchange="loadErrors()">
                                <option value="all">All Levels</option>
                                <option value="error">Error</option>
                                <option value="critical">Critical</option>
                                <option value="emergency">Emergency</option>
                                <option value="alert">Alert</option>
                                <option value="warning">Warning</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Limit</label>
                            <select class="form-select" id="errorLimit" onchange="loadErrors()">
                                <option value="50">50 Errors</option>
                                <option value="100" selected>100 Errors</option>
                                <option value="200">200 Errors</option>
                                <option value="500">500 Errors</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="errorSearch" 
                                   placeholder="Search errors by message, file, or trace..." 
                                   onkeyup="debounceSearch()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Errors Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul"></i> Error Logs
                        <span class="badge bg-danger ms-2" id="errorCount">0</span>
                    </h5>
                    <div class="text-muted small">
                        <i class="bx bx-time"></i> Last updated: <span id="lastUpdated">-</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="errorsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th width="180">Timestamp</th>
                                    <th width="100">Level</th>
                                    <th>Message</th>
                                    <th width="120" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="errorsList">
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Detail Modal -->
<div class="modal fade" id="errorDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-error-circle text-danger"></i> Error Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Timestamp:</strong> <span id="detailTimestamp"></span>
                </div>
                <div class="mb-3">
                    <strong>Level:</strong> <span id="detailLevel"></span>
                </div>
                <div class="mb-3">
                    <strong>Message:</strong>
                    <pre class="bg-light p-3 rounded" id="detailMessage"></pre>
                </div>
                <div class="mb-3">
                    <strong>Details:</strong>
                    <pre class="bg-light p-3 rounded" id="detailDetails" style="max-height: 200px; overflow-y: auto;"></pre>
                </div>
                <div>
                    <strong>Stack Trace:</strong>
                    <pre class="bg-dark text-light p-3 rounded" id="detailTrace" style="max-height: 300px; overflow-y: auto; font-size: 12px;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-danger {
        border-left: 4px solid #dc3545;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    .border-left-info {
        border-left: 4px solid #0dcaf0;
    }
    .border-left-primary {
        border-left: 4px solid #0d6efd;
    }
    .badge-error {
        background-color: #dc3545;
    }
    .badge-critical {
        background-color: #721c24;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }
    .badge-emergency {
        background-color: #000;
    }
    .badge-alert {
        background-color: #fd7e14;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let searchTimeout;

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadErrors();
    }, 500);
}

function loadErrors() {
    const level = document.getElementById('errorLevel').value;
    const limit = document.getElementById('errorLimit').value;
    const search = document.getElementById('errorSearch').value;
    
    // Show loading state
    const tbody = document.getElementById('errorsList');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    
    fetch(`{{ route('admin.system.errors') }}?level=${level}&limit=${limit}&search=${encodeURIComponent(search)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayErrors(data.errors);
                document.getElementById('errorCount').textContent = data.errors.length;
                document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Failed to load errors</td></tr>';
            }
        })
        .catch(err => {
            console.error('Error loading errors:', err);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Error loading errors. Please try again.</td></tr>';
        });
}

function displayErrors(errors) {
    const tbody = document.getElementById('errorsList');
    
    if (errors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No errors found</td></tr>';
        return;
    }
    
    tbody.innerHTML = errors.map((error, index) => {
        const levelClass = getLevelClass(error.level);
        const levelBadge = getLevelBadge(error.level);
        
        return `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <small>${error.timestamp}</small>
                    <br>
                    <small class="text-muted">${getTimeAgo(error.timestamp)}</small>
                </td>
                <td>${levelBadge}</td>
                <td>
                    <div class="text-truncate" style="max-width: 500px;" title="${escapeHtml(error.message)}">
                        ${escapeHtml(error.message)}
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" onclick="showErrorDetail(${index})" title="View Details">
                        <i class="bx bx-show"></i> Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Store errors globally for detail view
    window.errorsData = errors;
}

function getLevelClass(level) {
    const classes = {
        'error': 'text-danger',
        'critical': 'text-danger fw-bold',
        'emergency': 'text-dark fw-bold',
        'alert': 'text-warning',
        'warning': 'text-warning',
    };
    return classes[level] || 'text-secondary';
}

function getLevelBadge(level) {
    const badges = {
        'error': '<span class="badge bg-danger">ERROR</span>',
        'critical': '<span class="badge bg-dark">CRITICAL</span>',
        'emergency': '<span class="badge bg-dark">EMERGENCY</span>',
        'alert': '<span class="badge bg-warning text-dark">ALERT</span>',
        'warning': '<span class="badge bg-warning text-dark">WARNING</span>',
    };
    return badges[level] || '<span class="badge bg-secondary">' + level.toUpperCase() + '</span>';
}

function getTimeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
}

function showErrorDetail(index) {
    const error = window.errorsData[index];
    if (!error) return;
    
    document.getElementById('detailTimestamp').textContent = error.timestamp;
    document.getElementById('detailLevel').innerHTML = getLevelBadge(error.level);
    document.getElementById('detailMessage').textContent = error.message;
    document.getElementById('detailDetails').textContent = error.details ? error.details.join('\n') : 'No additional details';
    document.getElementById('detailTrace').textContent = error.trace || 'No stack trace available';
    
    const modal = new bootstrap.Modal(document.getElementById('errorDetailModal'));
    modal.show();
}

function refreshErrors() {
    loadErrors();
    loadStatistics();
}

function loadStatistics() {
    fetch('{{ route("admin.system.errors.statistics") }}')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalErrors').textContent = data.stats.total_errors.toLocaleString();
                document.getElementById('errorsToday').textContent = data.stats.errors_today.toLocaleString();
                document.getElementById('errorsWeek').textContent = data.stats.errors_this_week.toLocaleString();
                document.getElementById('errorsMonth').textContent = data.stats.errors_this_month.toLocaleString();
            }
        })
        .catch(err => console.error('Error loading statistics:', err));
}

function downloadLogs() {
    window.location.href = '{{ route("admin.system.errors.download") }}';
}

function clearLogs() {
    Swal.fire({
        title: 'Clear All Logs?',
        text: 'This will permanently delete all error logs. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, clear all logs!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("admin.system.errors.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Cleared!', data.message, 'success');
                    loadErrors();
                    loadStatistics();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to clear logs', 'error');
            });
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadErrors();
    loadStatistics();
    
    // Auto-refresh every 30 seconds
    setInterval(() => {
        loadErrors();
        loadStatistics();
    }, 30000);
});
</script>
@endpush

