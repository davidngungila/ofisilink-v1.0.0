@extends('layouts.app')

@section('title', 'System Status & Health - Admin')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">System Status & Health</h4>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Success Message -->
    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Live User Statistics -->
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-user-check"></i> Live User Statistics
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshLiveStats()">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="row" id="liveStatsContainer">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="liveLoggedIn">{{ $liveStats['live_logged_in'] ?? 0 }}</h3>
                                <small>Users Logged In</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="currentlyWorking">{{ $liveStats['currently_working'] ?? 0 }}</h3>
                                <small>Currently Working (Last 15 min)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="totalUsers">{{ $liveStats['total_users'] ?? 0 }}</h3>
                                <small>Total Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="recentLogins">{{ $liveStats['recent_logins_24h'] ?? 0 }}</h3>
                                <small>Recent Logins (24h)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-heart"></i> System Health Status
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshHealth()">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div id="healthStatus">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td width="40%"><strong>Database</strong></td>
                                <td>
                                    @if($health['checks']['database']['ok'] ?? false)
                                    <span class="badge bg-success"><i class="bx bx-check"></i> OK</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bx bx-x"></i> Failed</span>
                                    <small class="text-danger d-block">{{ $health['checks']['database']['error'] ?? 'Connection error' }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cache</strong></td>
                                <td>
                                    @if($health['checks']['cache']['ok'] ?? false)
                                    <span class="badge bg-success"><i class="bx bx-check"></i> OK</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bx bx-x"></i> Failed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Mail Service</strong></td>
                                <td>
                                    @if($health['checks']['mail']['ok'] ?? false)
                                    <span class="badge bg-success"><i class="bx bx-check"></i> OK</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bx bx-x"></i> Failed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Storage</strong></td>
                                <td>
                                    @if($health['checks']['storage']['ok'] ?? false)
                                    <span class="badge bg-success"><i class="bx bx-check"></i> OK</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bx bx-x"></i> Failed</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Environment</small>
                            <div class="fw-bold">{{ $health['app']['env'] ?? 'Unknown' }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Laravel Version</small>
                            <div class="fw-bold">{{ $health['app']['version'] ?? 'Unknown' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-info-circle"></i> System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <td width="50%"><strong>PHP Version</strong></td>
                                <td>{{ $systemInfo['server']['php_version'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Server OS</strong></td>
                                <td>{{ $systemInfo['server']['os'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit</strong></td>
                                <td>{{ $systemInfo['server']['memory_limit'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time</strong></td>
                                <td>{{ $systemInfo['server']['max_execution_time'] ?? 'N/A' }}s</td>
                            </tr>
                            <tr>
                                <td><strong>Database Size</strong></td>
                                <td>
                                    @if(isset($systemInfo['database']['size_mb']))
                                        {{ number_format($systemInfo['database']['size_mb'], 2) }} MB
                                        ({{ number_format($systemInfo['database']['size_gb'], 2) }} GB)
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Database Tables</strong></td>
                                <td>{{ $systemInfo['database']['tables'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Storage Used</strong></td>
                                <td>
                                    @if(isset($systemInfo['storage']['used_gb']))
                                        {{ number_format($systemInfo['storage']['used_gb'], 2) }} GB
                                        ({{ number_format($systemInfo['storage']['used_percent'], 1) }}%)
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Storage Free</strong></td>
                                <td>
                                    @if(isset($systemInfo['storage']['free_gb']))
                                        {{ number_format($systemInfo['storage']['free_gb'], 2) }} GB
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Queue Driver</strong></td>
                                <td>{{ $systemInfo['queue']['driver'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Timezone</strong></td>
                                <td>{{ $systemInfo['application']['timezone'] ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Section: Performance & System Metrics -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-cog"></i> Advanced System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Performance Metrics -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h6 class="mb-3"><i class="bx bx-tachometer me-2"></i>Performance Metrics</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="60%"><strong>Database Queries</strong></td>
                                        <td>{{ $performanceMetrics['database']['query_count'] ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Query Time</strong></td>
                                        <td>{{ number_format($performanceMetrics['database']['total_time_ms'] ?? 0, 2) }} ms</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Avg Query Time</strong></td>
                                        <td>{{ number_format($performanceMetrics['database']['avg_time_ms'] ?? 0, 2) }} ms</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cache Driver</strong></td>
                                        <td>
                                            <span class="badge bg-{{ ($performanceMetrics['cache']['status'] ?? 'inactive') == 'active' ? 'success' : 'secondary' }}">
                                                {{ $performanceMetrics['cache']['driver'] ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- System Metrics -->
                    <div class="col-lg-6">
                        <h6 class="mb-3"><i class="bx bx-chip me-2"></i>System Metrics</h6>
                        <div class="mb-3">
                            <strong>Memory Usage:</strong>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar {{ ($advancedMetrics['memory']['usage_percent'] ?? 0) > 80 ? 'bg-danger' : (($advancedMetrics['memory']['usage_percent'] ?? 0) > 60 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ min($advancedMetrics['memory']['usage_percent'] ?? 0, 100) }}%">
                                    {{ number_format($advancedMetrics['memory']['usage_percent'] ?? 0, 1) }}%
                                </div>
                            </div>
                            <small class="text-muted">
                                Current: {{ number_format($advancedMetrics['memory']['current_mb'] ?? 0, 2) }} MB | 
                                Peak: {{ number_format($advancedMetrics['memory']['peak_mb'] ?? 0, 2) }} MB | 
                                Limit: {{ $advancedMetrics['memory']['limit_mb'] ?? 'Unlimited' }} {{ is_numeric($advancedMetrics['memory']['limit_mb'] ?? null) ? 'MB' : '' }}
                            </small>
                        </div>
                        
                        @if(isset($advancedMetrics['system_load']))
                        <div class="mb-3">
                            <strong>System Load:</strong>
                            <div class="mt-2">
                                <div class="d-flex gap-3">
                                    <div>
                                        <small class="text-muted d-block">1min</small>
                                        <strong>{{ $advancedMetrics['system_load']['1min'] ?? 'N/A' }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">5min</small>
                                        <strong>{{ $advancedMetrics['system_load']['5min'] ?? 'N/A' }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">15min</small>
                                        <strong>{{ $advancedMetrics['system_load']['15min'] ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <strong>PHP Extensions:</strong>
                            <div class="mt-2">
                                @foreach($advancedMetrics['extensions'] ?? [] as $ext => $loaded)
                                    <span class="badge bg-{{ $loaded ? 'success' : 'danger' }} me-1 mb-1">
                                        {{ $ext }} <i class="bx bx-{{ $loaded ? 'check' : 'x' }}"></i>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile App Sessions -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-mobile"></i> Mobile App Sessions
                    </h5>
                    <p class="text-muted mb-0">All active mobile app user sessions</p>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-danger" onclick="revokeAllMobileSessions()" title="Revoke All Mobile Sessions">
                        <i class="bx bx-log-out"></i> Revoke All Mobile
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadMobileAppSessions()">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Device Type</th>
                                <th>App Version</th>
                                <th>IP Address</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mobileSessionsTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                    <div class="text-muted small" id="mobileSessionsInfo">Loading mobile app sessions...</div>
                    <div class="text-muted small mt-2 mt-md-0">
                        <i class="bx bx-info-circle me-1"></i>
                        <span>Shows all active mobile app sessions (Android & iOS)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Events -->
    @if(!empty($recentEvents))
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-time-five"></i> Recent System Events
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach(array_slice($recentEvents, 0, 5) as $event)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx {{ $event['icon'] ?? 'bx-info-circle' }}"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small">{{ $event['title'] ?? 'Unknown Event' }}</h6>
                                <p class="mb-1 small text-muted">{{ strlen($event['description'] ?? '') > 60 ? substr($event['description'] ?? '', 0, 60) . '...' : ($event['description'] ?? '') }}</p>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($event['time'] ?? now())->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Errors -->
    @if(!empty($advancedMetrics['recent_errors']))
    <div class="col-lg-6 mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-error-circle"></i> Recent System Errors
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach(array_slice($advancedMetrics['recent_errors'], 0, 5) as $error)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-error"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1 small">{{ strlen($error['message'] ?? 'Unknown error') > 100 ? substr($error['message'] ?? 'Unknown error', 0, 100) . '...' : ($error['message'] ?? 'Unknown error') }}</p>
                                @if(isset($error['time']))
                                <small class="text-muted">{{ $error['time'] }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- User Management Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-user-x"></i> User Management
                    </h5>
                    <p class="text-muted mb-0">Manage active users, block or remove them from the system</p>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="loadUsers()">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control form-control-sm" id="userSearch" 
                               placeholder="Name, Email, Employee ID, Phone..." onkeyup="debounceSearch()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" id="userStatus" onchange="loadUsers()">
                            <option value="">All Users</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Per Page</label>
                        <select class="form-select form-select-sm" id="userPerPage" onchange="loadUsers()">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" onclick="loadUsers()">
                            <i class="bx bx-search"></i> Search
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAllUsers" onchange="toggleSelectAllUsers()">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Employee ID</th>
                                <th>Phone</th>
                                <th>Roles</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Blocked Until</th>
                                <th class="text-center" width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="usersInfo">Loading...</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="usersPagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions Management Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-devices"></i> Active Sessions Management
                    </h5>
                    <p class="text-muted mb-0">View and manage all active user sessions</p>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-danger" onclick="revokeAllSessions()" title="Revoke All Sessions">
                        <i class="bx bx-log-out"></i> Revoke All (Except Current)
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadActiveSessions()">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sessionsTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="sessionsInfo">Loading sessions...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Backup Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-data"></i> Advanced Database Backup Management
                    </h5>
                    <p class="text-muted mb-0">Full database backup with password protection and advanced features</p>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadBackupStats()">
                        <i class="bx bx-stats"></i> View Statistics
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Backup Statistics -->
                <div class="row mb-4" id="backupStatsContainer" style="display: none;">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="backupTotalCount">0</h3>
                                <small>Total Backups</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="backupTotalSize">0 MB</h3>
                                <small>Total Size</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="backupLastBackup">N/A</h3>
                                <small>Last Backup</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0" id="backupOldestBackup">N/A</h3>
                                <small>Oldest Backup</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i>
                    <strong>Advanced Backup Features:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Format:</strong> Normal SQL file (not compressed)</li>
                        <li><strong>Password Protection:</strong> <code>Ofisilink</code> (case-sensitive) - for database access</li>
                        <li><strong>Filename:</strong> Includes timestamp and current year (e.g., ofisilink_backup_20250101_120000_2025.sql)</li>
                        <li><strong>Notifications:</strong> Automatic SMS and Email (with attachment) to all System Administrators and davidngungila@gmail.com</li>
                        <li><strong>Auto-cleanup:</strong> Old backups are automatically removed based on retention policy</li>
                        <li><strong>Fallback:</strong> Uses Laravel DB connection if mysqldump is unavailable</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle"></i>
                    <strong>Backup Cleanup:</strong> Backup cleanup feature requires backend implementation. Old backups are automatically cleaned based on retention policy. You can also manually delete backups using the delete button in the backups list below.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary btn-lg w-100" id="backupBtn" onclick="triggerBackup()">
                            <i class="bx bx-download"></i> Run Backup Now
                        </button>
                        <div id="backupProgress" class="mt-3" style="display: none;">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" 
                                     style="width: 100%">
                                    <strong>Creating backup... This may take a few minutes</strong>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Please wait while the backup is being created. Do not close this page.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="backupResult" class="alert" style="display: none;"></div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bx bx-time"></i> Estimated time: 2-5 minutes depending on database size
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Backup Schedule Configuration -->
                <div class="mt-4">
                    <h6 class="mb-3">
                        <i class="bx bx-calendar"></i> Backup Schedule Configuration
                    </h6>
                    <form id="backupScheduleForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Enable Auto Backup</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="backupEnabled" 
                                       {{ ($backupSchedule['enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="backupEnabled">Enabled</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Frequency</label>
                            <select class="form-select" id="backupFrequency">
                                <option value="daily" {{ ($backupSchedule['frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($backupSchedule['frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($backupSchedule['frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Schedule Time</label>
                            <input type="time" class="form-control" id="backupTime" 
                                   value="{{ $backupSchedule['time'] ?? '23:59' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Retention (Days)</label>
                            <input type="number" class="form-control" id="backupRetention" 
                                   value="{{ $backupSchedule['retention_days'] ?? 30 }}" min="1" max="365">
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-primary" onclick="saveBackupSchedule()">
                                <i class="bx bx-save"></i> Save Schedule
                            </button>
                            <small class="text-muted ms-2">Note: Schedule changes require server cron configuration update</small>
                        </div>
                    </form>
                </div>

                <!-- All Backups List -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <i class="bx bx-list-ul"></i> All Backups (Total: <span id="backupTotal">0</span>)
                        </h6>
                        <div>
                            <button class="btn btn-sm btn-outline-danger" onclick="cleanupOldBackups()" title="Delete backups older than retention period">
                                <i class="bx bx-trash"></i> Cleanup Old Backups
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="loadBackupsList()">
                                <i class="bx bx-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="backupsTable">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Age</th>
                                    <th class="text-center" width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="backupsList">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Loading backups...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let backupInProgress = false;

function refreshHealth() {
    fetch('{{ route("admin.system.health") }}')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
}

function refreshLiveStats() {
    fetch('{{ route("admin.system.live-stats") }}')
        .then(res => res.json())
        .then(data => {
            if(data.success && data.stats) {
                document.getElementById('liveLoggedIn').textContent = data.stats.live_logged_in || 0;
                document.getElementById('currentlyWorking').textContent = data.stats.currently_working || 0;
                document.getElementById('totalUsers').textContent = data.stats.total_users || 0;
                document.getElementById('recentLogins').textContent = data.stats.recent_logins_24h || 0;
            }
        })
        .catch(err => console.error('Error refreshing live stats:', err));
}

async function triggerBackup() {
    if(backupInProgress) {
        Swal.fire('Warning', 'Backup is already in progress. Please wait...', 'warning');
        return;
    }

    const confirmed = await Swal.fire({
        title: 'Start Database Backup?',
        text: 'This will create a full database backup. All administrators will be notified via SMS and Email with attachment when complete.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Start Backup',
        cancelButtonText: 'Cancel'
    });

    if(!confirmed.isConfirmed) return;

    backupInProgress = true;
    const btn = document.getElementById('backupBtn');
    const progress = document.getElementById('backupProgress');
    const result = document.getElementById('backupResult');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Creating Backup...';
    progress.style.display = 'block';
    result.style.display = 'none';

    try {
        const response = await fetch('{{ route("admin.system.backup.now") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if(data.success) {
            progress.style.display = 'none';
            result.className = 'alert alert-success';
            result.style.display = 'block';
            result.innerHTML = `
                <i class="bx bx-check-circle"></i> 
                <strong>Backup Completed Successfully!</strong><br>
                <small>All System Administrators have been notified via SMS and Email with attachment.</small><br>
                ${data.download_url ? `
                    <a href="${data.download_url}" class="btn btn-sm btn-primary mt-2" download>
                        <i class="bx bx-download"></i> Download Backup File
                    </a>
                ` : `
                    <small class="text-muted">Download link will be available in the backups list below.</small>
                `}
            `;

            Swal.fire({
                title: 'Backup Completed!',
                html: `
                    <p>Database backup has been created successfully.</p>
                    <p><strong>All System Administrators have been notified via SMS and Email with attachment.</strong></p>
                    ${data.download_url ? `<p><a href="${data.download_url}" class="btn btn-primary mt-2" download>Download Backup</a></p>` : '<p class="text-warning">Download link will be available in the backups list.</p>'}
                `,
                icon: 'success',
                confirmButtonText: 'OK'
            });

            loadBackupsList();
        } else {
            progress.style.display = 'none';
            result.className = 'alert alert-danger';
            result.style.display = 'block';
            result.innerHTML = `<i class="bx bx-error-circle"></i> <strong>Backup Failed:</strong> ${data.message || 'Unknown error occurred'}`;
            
            Swal.fire('Error', data.message || 'Backup failed. Please try again.', 'error');
        }
    } catch(error) {
        progress.style.display = 'none';
        result.className = 'alert alert-danger';
        result.style.display = 'block';
        result.innerHTML = `<i class="bx bx-error-circle"></i> <strong>Error:</strong> ${error.message}`;
        
        Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
    } finally {
        backupInProgress = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-download"></i> Run Backup Now';
    }
}

function loadBackupsList() {
    console.log('loadBackupsList called');
    const tbody = document.getElementById('backupsList');
    const totalSpan = document.getElementById('backupTotal');
    
    if (!tbody) {
        console.error('Backups table body not found');
        return Promise.resolve();
    }
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    console.log('Fetching backups from:', '{{ route("admin.system.backup.list") }}');
    return fetch('{{ route("admin.system.backup.list") }}', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => {
        if (!res.ok) {
            return res.json().catch(() => ({ success: false, message: `HTTP Error: ${res.status}` }));
        }
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            const errorMessage = data.message || 'Unknown error occurred';
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading backups:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
            if (totalSpan) totalSpan.textContent = '0';
            return;
        }
        
        if(data.success && data.backups && data.backups.length > 0) {
                totalSpan.textContent = data.total || data.backups.length;
                
                // Calculate statistics
                let totalSize = 0;
                let lastBackup = null;
                let oldestBackup = null;
                
                data.backups.forEach(backup => {
                    // Parse size (e.g., "125.50 MB")
                    const sizeMatch = backup.size.match(/([\d.]+)\s*(\w+)/);
                    if (sizeMatch) {
                        const value = parseFloat(sizeMatch[1]);
                        const unit = sizeMatch[2].toUpperCase();
                        const multiplier = unit === 'GB' ? 1024 : (unit === 'KB' ? 0.001 : 1);
                        totalSize += value * multiplier;
                    }
                    
                    const backupDate = new Date(backup.created_at);
                    if (!lastBackup || backupDate > new Date(lastBackup)) {
                        lastBackup = backup.created_at;
                    }
                    if (!oldestBackup || backupDate < new Date(oldestBackup)) {
                        oldestBackup = backup.created_at;
                    }
                });
                
                // Update statistics
                const statsContainer = document.getElementById('backupStatsContainer');
                if (statsContainer) {
                    document.getElementById('backupTotalCount').textContent = data.backups.length;
                    document.getElementById('backupTotalSize').textContent = totalSize >= 1024 
                        ? (totalSize / 1024).toFixed(2) + ' GB' 
                        : totalSize.toFixed(2) + ' MB';
                    document.getElementById('backupLastBackup').textContent = lastBackup 
                        ? new Date(lastBackup).toLocaleDateString() 
                        : 'N/A';
                    document.getElementById('backupOldestBackup').textContent = oldestBackup 
                        ? new Date(oldestBackup).toLocaleDateString() 
                        : 'N/A';
                }
                
                // Calculate age for each backup
                tbody.innerHTML = data.backups.map((backup, index) => {
                    const backupDate = new Date(backup.created_at);
                    const ageDays = Math.floor((new Date() - backupDate) / (1000 * 60 * 60 * 24));
                    const ageText = ageDays === 0 ? 'Today' : (ageDays === 1 ? '1 day ago' : `${ageDays} days ago`);
                    
                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td><code>${backup.filename}</code></td>
                            <td>${backup.size}</td>
                            <td>${backup.created_at}</td>
                            <td><small class="text-muted">${ageText}</small></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    ${backup.download_url ? `
                                        <a href="${backup.download_url}" class="btn btn-sm btn-primary" download>
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                    ` : `
                                        <span class="btn btn-sm btn-secondary" disabled title="File not available">
                                            <i class="bx bx-x"></i> Not Available
                                        </span>
                                    `}
                                    <button class="btn btn-sm btn-danger" onclick="deleteBackup('${backup.filename}', ${backup.id})" title="Delete this backup">
                                        <i class="bx bx-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                totalSpan.textContent = '0';
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No backups found</td></tr>';
                
                // Reset statistics
                const statsContainer = document.getElementById('backupStatsContainer');
                if (statsContainer) {
                    document.getElementById('backupTotalCount').textContent = '0';
                    document.getElementById('backupTotalSize').textContent = '0 MB';
                    document.getElementById('backupLastBackup').textContent = 'N/A';
                    document.getElementById('backupOldestBackup').textContent = 'N/A';
                }
            }
        })
        .catch(err => {
            console.error('Error loading backups:', err);
            const errorMessage = err.message || 'Network error or server unavailable';
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">
                    <i class="bx bx-error-circle"></i><br>
                    <strong>Error loading backups:</strong><br>
                    <small>${escapeHtml(errorMessage)}</small>
                </td></tr>`;
            }
            if (totalSpan) totalSpan.textContent = '0';
        });
}

function saveBackupSchedule() {
    const enabled = document.getElementById('backupEnabled').checked;
    const frequency = document.getElementById('backupFrequency').value;
    const time = document.getElementById('backupTime').value;
    const retention = parseInt(document.getElementById('backupRetention').value);

    fetch('{{ route("admin.system.backup.schedule") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            enabled: enabled,
            frequency: frequency,
            time: time,
            retention_days: retention
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Success', data.message || 'Backup schedule updated successfully', 'success');
        } else {
            Swal.fire('Error', data.message || 'Failed to update backup schedule', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
    });
}

function deleteBackup(filename, backupId) {
    Swal.fire({
        title: 'Delete Backup?',
        text: `Are you sure you want to delete "${filename}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('admin/system/backup') }}/${backupId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Deleted!', data.message || 'Backup has been deleted.', 'success');
                    loadBackupsList();
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete backup.', 'error');
                }
            })
            .catch(err => {
                console.error('Error deleting backup:', err);
                Swal.fire('Error', 'Network error occurred. Please try again.', 'error');
            });
        }
    });
}

// Load backups on page load - merged with session loading below

// User Management Functions
let usersPage = 1;
let usersSearchTimeout = null;

function debounceSearch() {
    clearTimeout(usersSearchTimeout);
    usersSearchTimeout = setTimeout(() => {
        usersPage = 1;
        loadUsers();
    }, 500);
}

function loadUsers() {
    try {
        console.log('loadUsers called');
        const search = document.getElementById('userSearch')?.value || '';
        const status = document.getElementById('userStatus')?.value || '';
        const perPage = document.getElementById('userPerPage')?.value || 20;
        
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) {
            console.error('Users table body not found');
            return;
        }
        
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
        
        console.log('Fetching users from:', '{{ route('admin.system.users') }}');
        fetch('{{ route('admin.system.users') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                search: search,
                status: status,
                per_page: perPage,
                page: usersPage
            })
        })
    .then(res => {
        if (!res.ok) {
            return res.json().catch(() => ({ success: false, message: `HTTP Error: ${res.status} ${res.statusText}` }));
        }
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            const errorMessage = data.message || 'Unknown error occurred';
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading users:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
            document.getElementById('usersInfo').textContent = 'Error occurred';
            document.getElementById('usersPagination').innerHTML = '';
            console.error('Error loading users:', errorMessage);
            return;
        }
        
        if (!data.users || data.users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No users found</td></tr>';
            document.getElementById('usersInfo').textContent = 'No users found';
            document.getElementById('usersPagination').innerHTML = '';
            return;
        }
        
        tbody.innerHTML = data.users.map(user => {
            const isBlocked = user.is_blocked;
            const blockedUntil = user.blocked_until ? new Date(user.blocked_until).toLocaleString() : (user.blocked_at ? 'Forever' : '-');
            const statusBadge = isBlocked 
                ? '<span class="badge bg-danger">Blocked</span>'
                : (user.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>');
            
            return `
                <tr class="${isBlocked ? 'table-danger' : ''}">
                    <td><input type="checkbox" class="form-check-input user-checkbox" value="${user.id}"></td>
                    <td><strong>${escapeHtml(user.name || 'N/A')}</strong></td>
                    <td>${escapeHtml(user.email || 'N/A')}</td>
                    <td><code>${escapeHtml(user.employee_id || 'N/A')}</code></td>
                    <td>${escapeHtml(user.phone || 'N/A')}</td>
                    <td>${user.roles.map(r => `<span class="badge bg-info">${escapeHtml(r)}</span>`).join(' ')}</td>
                    <td>${escapeHtml(user.department || 'N/A')}</td>
                    <td>${statusBadge}</td>
                    <td><small>${blockedUntil}</small></td>
                    <td class="text-center">
                        ${isBlocked 
                            ? `<button class="btn btn-sm btn-success" onclick="unblockUser(${user.id})" title="Unblock">
                                    <i class="bx bx-lock-open"></i>
                                </button>`
                            : `<button class="btn btn-sm btn-warning" onclick="blockUserModal(${user.id})" title="Block">
                                    <i class="bx bx-lock"></i>
                                </button>`
                        }
                        <button class="btn btn-sm btn-danger" onclick="deleteUserModal(${user.id}, '${escapeHtml(user.name)}')" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update pagination
        if (data.pagination) {
            const currentPage = data.pagination.current_page;
            const lastPage = data.pagination.last_page;
            const perPage = data.pagination.per_page;
            const total = data.pagination.total;
            
            const pagination = document.getElementById('usersPagination');
            if (pagination) {
                let html = '';
                
                if (currentPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="usersPage = ${currentPage - 1}; loadUsers(); return false;">Previous</a></li>`;
                }
                
                for (let i = Math.max(1, currentPage - 2); i <= Math.min(lastPage, currentPage + 2); i++) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="usersPage = ${i}; loadUsers(); return false;">${i}</a>
                    </li>`;
                }
                
                if (currentPage < lastPage) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="usersPage = ${currentPage + 1}; loadUsers(); return false;">Next</a></li>`;
                }
                
                pagination.innerHTML = html;
            }
            
            document.getElementById('usersInfo').textContent = 
                `Showing ${((currentPage - 1) * perPage) + 1} - ${Math.min(currentPage * perPage, total)} of ${total} users`;
        }
    })
    .catch(err => {
        console.error('Error loading users:', err);
        const errorMessage = err.message || 'Network error or server unavailable';
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading users:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
        }
        const usersInfo = document.getElementById('usersInfo');
        if (usersInfo) usersInfo.textContent = 'Error occurred';
        const usersPagination = document.getElementById('usersPagination');
        if (usersPagination) usersPagination.innerHTML = '';
    });
    } catch (error) {
        console.error('Error in loadUsers function:', error);
        const tbody = document.getElementById('usersTableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>JavaScript Error:</strong><br>
                <small>${escapeHtml(error.message || 'Unknown error')}</small>
            </td></tr>`;
        }
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return (text || '').replace(/[&<>"']/g, m => map[m]);
}

function blockUserModal(userId) {
    Swal.fire({
        title: 'Block User',
        html: `
            <div class="mb-3">
                <label class="form-label">Block Duration</label>
                <select class="form-select" id="blockDurationType">
                    <option value="forever">Forever (Permanent)</option>
                    <option value="hours">Hours</option>
                    <option value="days">Days</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                </select>
            </div>
            <div class="mb-3" id="blockDurationValueContainer" style="display: none;">
                <label class="form-label">Duration Value</label>
                <input type="number" class="form-control" id="blockDurationValue" min="1" value="1">
            </div>
            <div class="mb-3">
                <label class="form-label">Reason (Optional)</label>
                <textarea class="form-control" id="blockReason" rows="3" placeholder="Enter reason for blocking..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Block User',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            document.getElementById('blockDurationType').addEventListener('change', function() {
                document.getElementById('blockDurationValueContainer').style.display = 
                    this.value === 'forever' ? 'none' : 'block';
            });
        },
        preConfirm: () => {
            const durationType = document.getElementById('blockDurationType').value;
            const durationValue = document.getElementById('blockDurationValue').value;
            const reason = document.getElementById('blockReason').value;
            
            if (durationType !== 'forever' && !durationValue) {
                Swal.showValidationMessage('Please enter a duration value');
                return false;
            }
            
            return {
                duration_type: durationType,
                duration_value: durationType !== 'forever' ? parseInt(durationValue) : null,
                reason: reason
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            blockUser(userId, result.value);
        }
    });
}

function blockUser(userId, data) {
    Swal.fire({
        title: 'Blocking User...',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/admin/system/users/${userId}/block`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', data.message || 'User blocked successfully', 'success').then(() => {
                loadUsers();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to block user', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Network error occurred', 'error');
    });
}

function unblockUser(userId) {
    Swal.fire({
        title: 'Unblock User?',
        text: 'Are you sure you want to unblock this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Unblock',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Unblocking User...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/admin/system/users/${userId}/unblock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message || 'User unblocked successfully', 'success').then(() => {
                        loadUsers();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to unblock user', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network error occurred', 'error');
            });
        }
    });
}

function deleteUserModal(userId, userName) {
    Swal.fire({
        title: 'Delete User?',
        html: `Are you sure you want to permanently delete <strong>${escapeHtml(userName)}</strong>?<br><br>This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Delete Permanently',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting User...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/admin/system/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message || 'User deleted successfully', 'success').then(() => {
                        loadUsers();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete user', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network error occurred', 'error');
            });
        }
    });
}

function toggleSelectAllUsers() {
    const selectAll = document.getElementById('selectAllUsers');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Active Sessions Management Functions
function loadActiveSessions() {
    try {
        console.log('loadActiveSessions called');
        const tbody = document.getElementById('sessionsTableBody');
        if (!tbody) {
            console.error('Sessions table body not found');
            return;
        }
        
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
        
        console.log('Fetching sessions from:', '{{ route("admin.system.sessions") }}');
        fetch('{{ route("admin.system.sessions") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
    .then(res => {
        if (!res.ok) {
            return res.json().catch(() => ({ success: false, message: `HTTP Error: ${res.status}` }));
        }
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            const errorMessage = data.message || 'Unknown error occurred';
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading sessions:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
            const sessionsInfo = document.getElementById('sessionsInfo');
            if (sessionsInfo) sessionsInfo.textContent = 'Error occurred';
            return;
        }
        
        if (!data.sessions || data.sessions.length === 0) {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No active sessions found</td></tr>';
            }
            const sessionsInfo = document.getElementById('sessionsInfo');
            if (sessionsInfo) sessionsInfo.textContent = 'No active sessions';
            return;
        }
        
        tbody.innerHTML = data.sessions.map(session => {
            const statusBadge = session.is_current 
                ? '<span class="badge bg-primary">Current Session</span>'
                : (session.is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Idle</span>');
            
            const userAgentShort = session.user_agent.length > 50 
                ? session.user_agent.substring(0, 50) + '...' 
                : session.user_agent;
            
            return `
                <tr class="${session.is_current ? 'table-primary' : ''}">
                    <td><strong>${escapeHtml(session.user_name)}</strong></td>
                    <td>${escapeHtml(session.user_email)}</td>
                    <td><code>${escapeHtml(session.ip_address)}</code></td>
                    <td><small title="${escapeHtml(session.user_agent)}">${escapeHtml(userAgentShort)}</small></td>
                    <td>
                        <small>${escapeHtml(session.last_activity)}</small><br>
                        <small class="text-muted">${escapeHtml(session.last_activity_human)}</small>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        ${!session.is_current 
                            ? `<button class="btn btn-sm btn-danger" onclick="revokeSession('${session.id}', '${escapeHtml(session.user_name)}')" title="Revoke Session">
                                    <i class="bx bx-log-out"></i>
                                </button>`
                            : '<span class="text-muted">Current</span>'
                        }
                    </td>
                </tr>
            `;
        }).join('');
        
        const sessionsInfo = document.getElementById('sessionsInfo');
        if (sessionsInfo) sessionsInfo.textContent = `Showing ${data.total} active session(s)`;
    })
    .catch(err => {
        console.error('Error loading sessions:', err);
        const errorMessage = err.message || 'Network error or server unavailable';
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading sessions:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
        }
        const sessionsInfo = document.getElementById('sessionsInfo');
        if (sessionsInfo) sessionsInfo.textContent = 'Error occurred';
    });
    } catch (error) {
        console.error('Error in loadActiveSessions function:', error);
        const tbody = document.getElementById('sessionsTableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>JavaScript Error:</strong><br>
                <small>${escapeHtml(error.message || 'Unknown error')}</small>
            </td></tr>`;
        }
        const sessionsInfo = document.getElementById('sessionsInfo');
        if (sessionsInfo) sessionsInfo.textContent = 'Error occurred';
    }
}

// Mobile App Sessions Management Functions
function loadMobileAppSessions() {
    const tbody = document.getElementById('mobileSessionsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    fetch('{{ route("admin.system.sessions") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => {
        if (!res.ok) {
            return res.json().catch(() => ({ success: false, message: `HTTP Error: ${res.status}` }));
        }
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            const errorMessage = data.message || 'Unknown error occurred';
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">
                <i class="bx bx-error-circle"></i><br>
                <strong>Error loading mobile app sessions:</strong><br>
                <small>${escapeHtml(errorMessage)}</small>
            </td></tr>`;
            document.getElementById('mobileSessionsInfo').textContent = 'Error occurred';
            return;
        }
        
        // Filter only mobile app sessions
        const mobileSessions = (data.sessions || []).filter(session => session.is_mobile_app === true);
        
        if (mobileSessions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No active mobile app sessions found</td></tr>';
            document.getElementById('mobileSessionsInfo').textContent = 'No mobile app sessions';
            return;
        }
        
        tbody.innerHTML = mobileSessions.map(session => {
            const statusBadge = session.is_current 
                ? '<span class="badge bg-primary">Current</span>'
                : (session.is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Idle</span>');
            
            const deviceBadge = session.device_type === 'Android' 
                ? '<span class="badge bg-success"><i class="bx bxl-android"></i> Android</span>'
                : session.device_type === 'iOS'
                ? '<span class="badge bg-info"><i class="bx bxl-apple"></i> iOS</span>'
                : '<span class="badge bg-secondary">' + escapeHtml(session.device_type) + '</span>';
            
            return `
                <tr class="${session.is_current ? 'table-primary' : ''}">
                    <td><strong>${escapeHtml(session.user_name)}</strong></td>
                    <td>${escapeHtml(session.user_email)}</td>
                    <td>${deviceBadge}</td>
                    <td>${session.app_version ? '<code>' + escapeHtml(session.app_version) + '</code>' : '<span class="text-muted">N/A</span>'}</td>
                    <td><code>${escapeHtml(session.ip_address)}</code></td>
                    <td>
                        <small>${escapeHtml(session.last_activity)}</small><br>
                        <small class="text-muted">${escapeHtml(session.last_activity_human)}</small>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        ${!session.is_current 
                            ? `<button class="btn btn-sm btn-danger" onclick="revokeSession('${session.id}', '${escapeHtml(session.user_name)}')" title="Revoke Session">
                                    <i class="bx bx-log-out"></i>
                                </button>`
                            : '<span class="text-muted">Current</span>'
                        }
                    </td>
                </tr>
            `;
        }).join('');
        
        document.getElementById('mobileSessionsInfo').textContent = `Showing ${mobileSessions.length} mobile app session(s)`;
    })
    .catch(err => {
        console.error('Error loading mobile app sessions:', err);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Error loading mobile app sessions</td></tr>';
        document.getElementById('mobileSessionsInfo').textContent = 'Error occurred';
    });
}

function revokeAllMobileSessions() {
    Swal.fire({
        title: 'Revoke All Mobile Sessions?',
        text: 'This will log out all users from mobile apps. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Revoke All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("admin.system.sessions") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.sessions) {
                    const mobileSessions = data.sessions.filter(s => s.is_mobile_app === true && !s.is_current);
                    let revoked = 0;
                    let errors = 0;
                    
                    if (mobileSessions.length === 0) {
                        Swal.fire('Info', 'No mobile app sessions to revoke', 'info');
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Revoking Sessions...',
                        text: `Revoking ${mobileSessions.length} mobile app session(s)...`,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    const revokePromises = mobileSessions.map(session => {
                        return fetch(`/admin/system/sessions/${session.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) revoked++;
                            else errors++;
                        })
                        .catch(() => errors++);
                    });
                    
                    Promise.all(revokePromises).then(() => {
                        Swal.fire('Success', `Revoked ${revoked} mobile app session(s)${errors > 0 ? '. ' + errors + ' failed.' : ''}`, 'success').then(() => {
                            loadMobileAppSessions();
                        });
                    });
                } else {
                    Swal.fire('Error', 'Failed to load sessions', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network error occurred', 'error');
            });
        }
    });
}
        const errorMessage = err.message || 'Network error or server unavailable';
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">
            <i class="bx bx-error-circle"></i><br>
            <strong>Error loading sessions:</strong><br>
            <small>${escapeHtml(errorMessage)}</small>
        </td></tr>`;
        document.getElementById('sessionsInfo').textContent = 'Error occurred';
    });
}

function revokeSession(sessionId, userName) {
    Swal.fire({
        title: 'Revoke Session?',
        html: `Are you sure you want to revoke the session for <strong>${escapeHtml(userName)}</strong>?<br><br>This will log them out immediately.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Revoke Session',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Revoking Session...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/admin/system/sessions/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message || 'Session revoked successfully', 'success').then(() => {
                        loadActiveSessions();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to revoke session', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network error occurred', 'error');
            });
        }
    });
}

function revokeAllSessions() {
    Swal.fire({
        title: 'Revoke All Sessions?',
        html: 'Are you sure you want to revoke ALL active sessions?<br><br><strong>Your current session will be preserved.</strong><br>All other users will be logged out immediately.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Revoke All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Revoking All Sessions...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('{{ route("admin.system.sessions.revoke-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message || `All sessions revoked successfully (${data.revoked_count || 0} sessions)`, 'success').then(() => {
                        loadActiveSessions();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to revoke sessions', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Network error occurred', 'error');
            });
        }
    });
}

// Enhanced Backup Functions
function loadBackupStats() {
    loadBackupsList().then(() => {
        const container = document.getElementById('backupStatsContainer');
        if (container) {
            container.style.display = 'block';
        }
    });
}

function cleanupOldBackups() {
    Swal.fire({
        title: 'Cleanup Old Backups?',
        html: 'This will delete backups older than the retention period. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Cleanup',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Cleaning up...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // This would require a backend endpoint - for now just show message
            setTimeout(() => {
                Swal.fire('Info', 'Backup cleanup feature requires backend implementation. Old backups are automatically cleaned based on retention policy.', 'info');
            }, 1000);
        }
    });
}

// Load sessions on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Loading system data...');
    try {
        loadBackupsList();
        loadUsers();
        loadActiveSessions();
        loadMobileAppSessions();
    } catch (error) {
        console.error('Error loading system data:', error);
    }
    
    // Auto-refresh removed per user request
    // Users can manually refresh using the refresh buttons if needed
});
</script>
@endpush
