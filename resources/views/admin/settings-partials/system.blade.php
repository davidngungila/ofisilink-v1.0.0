<!-- Advanced System Settings Page -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1"><i class="bx bx-cog me-2"></i> Advanced System Settings</h4>
                <p class="text-muted mb-0">Manage system-wide configurations and preferences</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="refreshSettings()" title="Refresh">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#systemSettingModal" onclick="openSystemSettingModal()">
                    <i class="bx bx-plus"></i> Add New Setting
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Settings Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-flash me-2"></i> Quick Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                        $otpTimeout = \App\Models\SystemSetting::getValue('otp_timeout_minutes', 10);
                        $maxLoginAttempts = \App\Models\SystemSetting::getValue('max_login_attempts', 5);
                        $sessionTimeout = \App\Models\SystemSetting::getValue('session_timeout_minutes', 120);
                        $orgSettings = \App\Models\OrganizationSetting::getSettings();
                        $timezone = $orgSettings->timezone ?? 'Africa/Dar_es_Salaam';
                        $dateFormat = \App\Models\SystemSetting::getValue('date_format', 'Y-m-d');
                        $timeFormat = \App\Models\SystemSetting::getValue('time_format', 'H:i:s');
                        $currency = \App\Models\SystemSetting::getValue('currency', 'TZS');
                        $currencySymbol = \App\Models\SystemSetting::getValue('currency_symbol', 'TSh');
                        $maxFileSize = \App\Models\SystemSetting::getValue('max_file_size', 10);
                        $allowedFileTypes = \App\Models\SystemSetting::getValue('allowed_file_types', 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx');
                        $backupSchedule = \App\Models\SystemSetting::getValue('backup_schedule', 'daily');
                        $backupScheduleTime = \App\Models\SystemSetting::getValue('backup_schedule_time', '23:59');
                        $backupAutoEnabled = \App\Models\SystemSetting::getValue('backup_auto_enabled', true);
                        $backupRetentionDays = \App\Models\SystemSetting::getValue('backup_retention_days', 30);
                        $attendanceRetentionDays = \App\Models\SystemSetting::getValue('attendance_data_retention_days', 365);
                        $attendanceFailureThreshold = \App\Models\SystemSetting::getValue('attendance_failure_threshold', 5);
                    @endphp
                    
                    <!-- Security & Authentication Section -->
                    <div class="col-12">
                        <h6 class="text-muted mb-3"><i class="bx bx-shield me-2"></i> Security & Authentication</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-time-five fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">OTP Timeout</h6>
                                        <small class="text-muted">Login & Verification</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Timeout (minutes)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_otp_timeout" value="{{ $otpTimeout }}" min="1" max="60">
                                        <button class="btn btn-primary" onclick="updateQuickSetting('otp_timeout_minutes', 'number', document.getElementById('quick_otp_timeout').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Controls how long OTP codes remain valid</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-shield fs-4 text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Max Login Attempts</h6>
                                        <small class="text-muted">Authentication</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Attempts</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_max_attempts" value="{{ $maxLoginAttempts }}" min="3" max="10">
                                        <button class="btn btn-success" onclick="updateQuickSetting('max_login_attempts', 'number', document.getElementById('quick_max_attempts').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Maximum failed login attempts before lockout</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-info bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-session fs-4 text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Session Timeout</h6>
                                        <small class="text-muted">User Sessions</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Timeout (minutes)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_session_timeout" value="{{ $sessionTimeout }}" min="15" max="1440">
                                        <button class="btn btn-info" onclick="updateQuickSetting('session_timeout_minutes', 'number', document.getElementById('quick_session_timeout').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Automatic logout after inactivity</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date & Time Section -->
                    <div class="col-12 mt-4">
                        <h6 class="text-muted mb-3"><i class="bx bx-calendar me-2"></i> Date & Time Settings</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-time fs-4 text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Timezone</h6>
                                        <small class="text-muted">System Timezone</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Timezone</label>
                                    <div class="input-group">
                                        <select class="form-select" id="quick_timezone">
                                            <option value="Africa/Dar_es_Salaam" {{ $timezone == 'Africa/Dar_es_Salaam' ? 'selected' : '' }}>Africa/Dar_es_Salaam</option>
                                            <option value="UTC" {{ $timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="Africa/Nairobi" {{ $timezone == 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi</option>
                                            <option value="Africa/Kampala" {{ $timezone == 'Africa/Kampala' ? 'selected' : '' }}>Africa/Kampala</option>
                                        </select>
                                        <button class="btn btn-warning" onclick="updateTimezoneSetting(document.getElementById('quick_timezone').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">System timezone for all operations</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-secondary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-calendar fs-4 text-secondary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Date Format</h6>
                                        <small class="text-muted">Display Format</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Format</label>
                                    <div class="input-group">
                                        <select class="form-select" id="quick_date_format">
                                            <option value="Y-m-d" {{ $dateFormat == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                            <option value="d/m/Y" {{ $dateFormat == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                            <option value="m/d/Y" {{ $dateFormat == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                            <option value="d-m-Y" {{ $dateFormat == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                        </select>
                                        <button class="btn btn-secondary" onclick="updateQuickSetting('date_format', 'text', document.getElementById('quick_date_format').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Date display format</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-dark bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-time fs-4 text-dark"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Time Format</h6>
                                        <small class="text-muted">Display Format</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Format</label>
                                    <div class="input-group">
                                        <select class="form-select" id="quick_time_format">
                                            <option value="H:i:s" {{ $timeFormat == 'H:i:s' ? 'selected' : '' }}>24 Hour (HH:MM:SS)</option>
                                            <option value="h:i:s A" {{ $timeFormat == 'h:i:s A' ? 'selected' : '' }}>12 Hour (HH:MM:SS AM/PM)</option>
                                        </select>
                                        <button class="btn btn-dark" onclick="updateQuickSetting('time_format', 'text', document.getElementById('quick_time_format').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Time display format</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Currency Section -->
                    <div class="col-12 mt-4">
                        <h6 class="text-muted mb-3"><i class="bx bx-dollar me-2"></i> Currency Settings</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-dollar fs-4 text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Currency Code</h6>
                                        <small class="text-muted">ISO Code</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="quick_currency" value="{{ $currency }}" maxlength="3" style="text-transform: uppercase;">
                                        <button class="btn btn-success" onclick="updateQuickSetting('currency', 'text', document.getElementById('quick_currency').value.toUpperCase())">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">ISO currency code (e.g., TZS, USD)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-money fs-4 text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Currency Symbol</h6>
                                        <small class="text-muted">Display Symbol</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Symbol</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="quick_currency_symbol" value="{{ $currencySymbol }}" maxlength="10">
                                        <button class="btn btn-success" onclick="updateQuickSetting('currency_symbol', 'text', document.getElementById('quick_currency_symbol').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Currency symbol (e.g., TSh, $)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div class="col-12 mt-4">
                        <h6 class="text-muted mb-3"><i class="bx bx-upload me-2"></i> File Upload Settings</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-file fs-4 text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Max File Size</h6>
                                        <small class="text-muted">Upload Limit</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Size (MB)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_max_file_size" value="{{ $maxFileSize }}" min="1" max="100">
                                        <button class="btn btn-danger" onclick="updateQuickSetting('max_file_size', 'number', document.getElementById('quick_max_file_size').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Maximum file upload size in MB</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-8">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-file-blank fs-4 text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Allowed File Types</h6>
                                        <small class="text-muted">Accepted Extensions</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Types (comma-separated)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="quick_allowed_file_types" value="{{ $allowedFileTypes }}">
                                        <button class="btn btn-danger" onclick="updateQuickSetting('allowed_file_types', 'text', document.getElementById('quick_allowed_file_types').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Comma-separated file extensions (e.g., pdf,jpg,png)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Backup Settings Section -->
                    <div class="col-12 mt-4">
                        <h6 class="text-muted mb-3"><i class="bx bx-data me-2"></i> Backup Settings</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-check-circle fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Auto Backup</h6>
                                        <small class="text-muted">Enable/Disable</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Enabled</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="quick_backup_enabled" {{ $backupAutoEnabled ? 'checked' : '' }} onchange="updateQuickSetting('backup_auto_enabled', 'boolean', this.checked ? '1' : '0')">
                                        <label class="form-check-label" for="quick_backup_enabled">{{ $backupAutoEnabled ? 'Enabled' : 'Disabled' }}</label>
                                    </div>
                                </div>
                                <small class="text-muted">Enable automatic backups</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-calendar fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Schedule</h6>
                                        <small class="text-muted">Frequency</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Frequency</label>
                                    <div class="input-group">
                                        <select class="form-select" id="quick_backup_schedule">
                                            <option value="daily" {{ $backupSchedule == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ $backupSchedule == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ $backupSchedule == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                        <button class="btn btn-primary" onclick="updateQuickSetting('backup_schedule', 'text', document.getElementById('quick_backup_schedule').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Backup frequency</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-time fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Schedule Time</h6>
                                        <small class="text-muted">Backup Time</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Time</label>
                                    <div class="input-group">
                                        <input type="time" class="form-control" id="quick_backup_time" value="{{ $backupScheduleTime }}">
                                        <button class="btn btn-primary" onclick="updateQuickSetting('backup_schedule_time', 'text', document.getElementById('quick_backup_time').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Time to run backup</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-history fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Retention Days</h6>
                                        <small class="text-muted">Keep Backups</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Days</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_backup_retention" value="{{ $backupRetentionDays }}" min="1" max="365">
                                        <button class="btn btn-primary" onclick="updateQuickSetting('backup_retention_days', 'number', document.getElementById('quick_backup_retention').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Days to retain backups</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Settings Section -->
                    <div class="col-12 mt-4">
                        <h6 class="text-muted mb-3"><i class="bx bx-user-check me-2"></i> Attendance Settings</h6>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-info bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-data fs-4 text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Data Retention</h6>
                                        <small class="text-muted">Attendance Records</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Days</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_attendance_retention" value="{{ $attendanceRetentionDays }}" min="30" max="3650">
                                        <button class="btn btn-info" onclick="updateQuickSetting('attendance_data_retention_days', 'number', document.getElementById('quick_attendance_retention').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Days to retain attendance data</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                                        <i class="bx bx-error fs-4 text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Failure Threshold</h6>
                                        <small class="text-muted">Attendance Failures</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Threshold</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quick_attendance_threshold" value="{{ $attendanceFailureThreshold }}" min="1" max="20">
                                        <button class="btn btn-warning" onclick="updateQuickSetting('attendance_failure_threshold', 'number', document.getElementById('quick_attendance_threshold').value)">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Max failures before alert</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" class="form-control" id="settingsSearch" placeholder="Search settings by key, value, or description..." onkeyup="filterSettings()">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="settingsTypeFilter" onchange="filterSettings()">
            <option value="">All Types</option>
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="boolean">Boolean</option>
            <option value="date">Date</option>
            <option value="email">Email</option>
            <option value="url">URL</option>
            <option value="textarea">Textarea</option>
        </select>
    </div>
    <div class="col-md-3">
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary w-100" onclick="exportSettings()">
                <i class="bx bx-export"></i> Export
            </button>
            <button class="btn btn-outline-secondary w-100" onclick="importSettings()">
                <i class="bx bx-import"></i> Import
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i> All System Settings</h5>
            <div class="text-muted small">
                <span id="settingsCount">{{ ($systemSettings ?? collect())->count() }}</span> settings found
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i> Settings List</h6>
                    <div class="text-muted small">
                        Total: <strong>{{ $systemSettings->count() }}</strong> settings
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="systemSettingsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="60">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th width="80">ID</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th width="120">Type</th>
                                <th>Description</th>
                                <th width="150">Last Updated</th>
                                <th width="150" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($systemSettings ?? [] as $setting)
                            <tr data-key="{{ strtolower($setting->key ?? '') }}" data-type="{{ $setting->type ?? 'text' }}" data-value="{{ strtolower($setting->value ?? '') }}" data-description="{{ strtolower($setting->description ?? '') }}">
                                <td>
                                    <input type="checkbox" class="setting-checkbox" value="{{ $setting->id }}">
                                </td>
                                <td><span class="badge bg-secondary">#{{ $setting->id }}</span></td>
                                <td>
                                    <code class="text-primary fw-bold">{{ $setting->key }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($setting->type === 'boolean')
                                            @php
                                                $boolValue = in_array(strtolower($setting->value), ['1', 'true', 'yes', 'on'], true) || $setting->value === 1 || $setting->value === true;
                                            @endphp
                                            <span class="badge {{ $boolValue ? 'bg-success' : 'bg-danger' }}">
                                                <i class="bx {{ $boolValue ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                                {{ $boolValue ? 'Yes' : 'No' }}
                                            </span>
                                        @elseif($setting->type === 'number')
                                            <span class="fw-bold text-info">
                                                <i class="bx bx-hash me-1"></i>
                                                {{ is_numeric($setting->value) ? number_format((float)$setting->value, 0, '.', ',') : $setting->value }}
                                            </span>
                                        @elseif($setting->type === 'email')
                                            <a href="mailto:{{ $setting->value }}" class="text-primary text-decoration-none">
                                                <i class="bx bx-envelope me-1"></i>{{ Str::limit($setting->value, 40) }}
                                            </a>
                                        @elseif($setting->type === 'url')
                                            <a href="{{ $setting->value }}" target="_blank" class="text-primary text-decoration-none">
                                                <i class="bx bx-link-external me-1"></i>{{ Str::limit($setting->value, 40) }}
                                            </a>
                                        @else
                                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $setting->value }}">
                                                {{ Str::limit($setting->value ?? '', 50) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'number' => 'info',
                                            'boolean' => 'warning',
                                            'email' => 'success',
                                            'url' => 'primary',
                                            'date' => 'secondary',
                                            'textarea' => 'dark'
                                        ];
                                        $color = $typeColors[$setting->type] ?? 'primary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        <i class="bx bx-{{ $setting->type === 'boolean' ? 'toggle' : ($setting->type === 'number' ? 'hash' : ($setting->type === 'email' ? 'envelope' : ($setting->type === 'url' ? 'link' : 'text'))) }} me-1"></i>
                                        {{ ucfirst($setting->type) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $setting->description ? Str::limit($setting->description, 60) : '-' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $setting->updated_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="editSystemSetting({{ $setting->id }})" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewSystemSetting({{ $setting->id }})" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteSystemSetting({{ $setting->id }})" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bx bx-cog fs-1 text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No system settings found. Click "Add New Setting" to create one.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(($systemSettings ?? collect())->count() > 0)
                <!-- Bulk Actions -->
                <div class="mt-3 d-none" id="bulkActions">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <span id="selectedCount">0</span> setting(s) selected
                                </span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-danger" onclick="bulkDeleteSettings()">
                                        <i class="bx bx-trash"></i> Delete Selected
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="clearSelection()">
                                        <i class="bx bx-x"></i> Clear Selection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                        </tbody>
                    </table>
                </div>
                
                <!-- Bulk Actions -->
                <div class="mt-3 d-none" id="bulkActions">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <span id="selectedCount">0</span> setting(s) selected
                                </span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-danger" onclick="bulkDeleteSettings()">
                                        <i class="bx bx-trash"></i> Delete Selected
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="clearSelection()">
                                        <i class="bx bx-x"></i> Clear Selection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Setting Modal -->
<div class="modal fade" id="systemSettingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="systemSettingForm">
                @csrf
                <input type="hidden" name="setting_id" id="setting_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="systemSettingModalTitle">
                        <i class="bx bx-cog me-2"></i>Add System Setting
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key <span class="text-danger">*</span></label>
                            <input type="text" name="key" id="setting_key" class="form-control" required placeholder="e.g., otp_timeout_minutes">
                            <small class="text-muted">Unique identifier (lowercase, underscores)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" id="setting_type" class="form-select" required onchange="updateValueInput()">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="date">Date</option>
                                <option value="email">Email</option>
                                <option value="url">URL</option>
                                <option value="textarea">Textarea</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <div id="valueInputContainer">
                            <textarea name="value" id="setting_value" class="form-control" rows="3" placeholder="Enter value"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="setting_description" class="form-control" rows="3" placeholder="Brief description of this setting and its purpose"></textarea>
                        <small class="text-muted">Help text for administrators</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Setting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateValueInput() {
    const type = document.getElementById('setting_type').value;
    const container = document.getElementById('valueInputContainer');
    const currentValue = document.getElementById('setting_value')?.value || '';
    
    let html = '';
    switch(type) {
        case 'boolean':
            html = `
                <select name="value" id="setting_value" class="form-select">
                    <option value="0" ${currentValue === '0' || currentValue === 'false' ? 'selected' : ''}>No / False</option>
                    <option value="1" ${currentValue === '1' || currentValue === 'true' ? 'selected' : ''}>Yes / True</option>
                </select>
            `;
            break;
        case 'number':
            html = `<input type="number" name="value" id="setting_value" class="form-control" value="${currentValue}" placeholder="Enter number">`;
            break;
        case 'date':
            html = `<input type="date" name="value" id="setting_value" class="form-control" value="${currentValue}">`;
            break;
        case 'email':
            html = `<input type="email" name="value" id="setting_value" class="form-control" value="${currentValue}" placeholder="email@example.com">`;
            break;
        case 'url':
            html = `<input type="url" name="value" id="setting_value" class="form-control" value="${currentValue}" placeholder="https://example.com">`;
            break;
        default:
            html = `<textarea name="value" id="setting_value" class="form-control" rows="3" placeholder="Enter value">${currentValue}</textarea>`;
    }
    
    container.innerHTML = html;
}
</script>

<script>
// Wait for jQuery to be available
(function() {
    function initSystemSettings() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(initSystemSettings, 100);
            return;
        }
        const $ = jQuery;
        
        // Double check jQuery is available
        if (!$ || typeof $.ajax === 'undefined') {
            setTimeout(initSystemSettings, 100);
            return;
        }
        
        // Update Timezone (stored in OrganizationSetting)
        window.updateTimezoneSetting = function(value) {
            $.ajax({
                url: '{{ route("settings.update") }}',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    timezone: value
                },
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Timezone updated successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update timezone', 'error');
                }
            });
        };
        
        // Quick Settings Update
        window.updateQuickSetting = function(key, type, value) {
            // Find existing setting or create new
            $.ajax({
                url: '{{ route("admin.settings.system") }}',
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        const existing = response.settings.find(s => s.key === key);
                        const url = existing 
                            ? '{{ route("admin.settings.system.update", ":id") }}'.replace(':id', existing.id)
                            : '{{ route("admin.settings.system.store") }}';
                        const method = existing ? 'PUT' : 'POST';
                        
                        const data = {
                            _token: '{{ csrf_token() }}',
                            key: key,
                            value: value,
                            type: type,
                            description: window.getQuickSettingDescription(key)
                        };
                        
                        $.ajax({
                            url: url,
                            method: method,
                            data: data,
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update setting', 'error');
                            }
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update setting', 'error');
                }
            });
        };
        
        // Make other jQuery-dependent functions available
        window.bulkDeleteSettings = function() {
            const selected = Array.from(document.querySelectorAll('.setting-checkbox:checked')).map(cb => cb.value);
            
            if (selected.length === 0) {
                Swal.fire('Warning!', 'Please select at least one setting to delete', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${selected.length} setting(s). This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let deleted = 0;
                    let failed = 0;
                    
                    selected.forEach(id => {
                        $.ajax({
                            url: '{{ route("admin.settings.system.delete", ":id") }}'.replace(':id', id),
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            async: false,
                            success: function() {
                                deleted++;
                            },
                            error: function() {
                                failed++;
                            }
                        });
                    });
                    
                    if (failed === 0) {
                        Swal.fire('Deleted!', `${deleted} setting(s) deleted successfully`, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Partial Success', `${deleted} deleted, ${failed} failed`, 'warning').then(() => {
                            location.reload();
                        });
                    }
                }
            });
        };
        
        window.viewSystemSetting = function(id) {
            $.ajax({
                url: '{{ route("admin.settings.system") }}',
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        const setting = response.settings.find(s => s.id == id);
                        if(setting) {
                            Swal.fire({
                                title: 'Setting Details',
                                html: `
                                    <div class="text-start">
                                        <p><strong>Key:</strong> <code>${setting.key}</code></p>
                                        <p><strong>Value:</strong> ${setting.value || '<em>Empty</em>'}</p>
                                        <p><strong>Type:</strong> <span class="badge bg-info">${setting.type}</span></p>
                                        <p><strong>Description:</strong> ${setting.description || '<em>No description</em>'}</p>
                                        <p><strong>Created:</strong> ${new Date(setting.created_at).toLocaleString()}</p>
                                        <p><strong>Updated:</strong> ${new Date(setting.updated_at).toLocaleString()}</p>
                                    </div>
                                `,
                                icon: 'info',
                                confirmButtonText: 'Edit Setting',
                                showCancelButton: true,
                                cancelButtonText: 'Close'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.editSystemSetting(id);
                                }
                            });
                        }
                    }
                }
            });
        };
        
        window.openSystemSettingModal = function() {
            $('#systemSettingForm')[0].reset();
            $('#setting_id').val('');
            $('#systemSettingModalTitle').text('Add System Setting');
        };
        
        window.editSystemSetting = function(id) {
            $.ajax({
                url: '{{ route("admin.settings.system") }}',
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        const setting = response.settings.find(s => s.id == id);
                        if(setting) {
                            $('#setting_id').val(setting.id);
                            $('#setting_key').val(setting.key);
                            $('#setting_value').val(setting.value);
                            $('#setting_type').val(setting.type);
                            $('#setting_description').val(setting.description);
                            $('#systemSettingModalTitle').text('Edit System Setting');
                            $('#systemSettingModal').modal('show');
                        }
                    }
                }
            });
        };
        
        window.deleteSystemSetting = function(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("admin.settings.system.delete", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to delete setting', 'error');
                        }
                    });
                }
            });
        };
    }
    
        // Non-jQuery helper function (can be global)
    window.getQuickSettingDescription = function(key) {
        const descriptions = {
            'otp_timeout_minutes': 'OTP code validity period in minutes. This setting controls how long OTP codes remain valid for login, password reset, and other verification purposes.',
            'max_login_attempts': 'Maximum number of failed login attempts allowed before account lockout.',
            'session_timeout_minutes': 'Automatic session timeout in minutes. Users will be logged out after this period of inactivity.',
            'timezone': 'System timezone for all date and time operations. Default: Africa/Dar_es_Salaam',
            'date_format': 'Date display format used throughout the system. Options: Y-m-d, d/m/Y, m/d/Y, d-m-Y',
            'time_format': 'Time display format. Options: H:i:s (24-hour) or h:i:s A (12-hour with AM/PM)',
            'currency': 'ISO currency code used for all financial operations (e.g., TZS, USD, EUR)',
            'currency_symbol': 'Currency symbol displayed with amounts (e.g., TSh, $, €)',
            'max_file_size': 'Maximum file upload size in megabytes (MB)',
            'allowed_file_types': 'Comma-separated list of allowed file extensions for uploads',
            'backup_auto_enabled': 'Enable or disable automatic database backups',
            'backup_schedule': 'Backup frequency: daily, weekly, or monthly',
            'backup_schedule_time': 'Time of day to run automatic backups (HH:MM format)',
            'backup_retention_days': 'Number of days to retain backup files before deletion',
            'attendance_data_retention_days': 'Number of days to retain attendance records in the database',
            'attendance_failure_threshold': 'Maximum number of attendance failures before generating an alert'
        };
        return descriptions[key] || '';
    };

function refreshSettings() {
    location.reload();
}

// Search and Filter
function filterSettings() {
    const search = document.getElementById('settingsSearch').value.toLowerCase();
    const typeFilter = document.getElementById('settingsTypeFilter').value;
    const rows = document.querySelectorAll('#systemSettingsTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.querySelector('.setting-checkbox')) {
            const key = row.getAttribute('data-key') || '';
            const value = row.getAttribute('data-value') || '';
            const description = row.getAttribute('data-description') || '';
            const type = row.getAttribute('data-type') || '';
            
            const matchesSearch = !search || key.includes(search) || value.includes(search) || description.includes(search);
            const matchesType = !typeFilter || type === typeFilter;
            
            if (matchesSearch && matchesType) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    document.getElementById('settingsCount').textContent = visibleCount;
}

// Select All
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.setting-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateBulkActions();
}

// Update selection count
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('setting-checkbox')) {
        updateBulkActions();
    }
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.setting-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkActions.classList.remove('d-none');
        selectedCount.textContent = selected.length;
    } else {
        bulkActions.classList.add('d-none');
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.setting-checkbox');
    const selectAll = document.getElementById('selectAll');
    if (allCheckboxes.length > 0) {
        selectAll.checked = selected.length === allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.setting-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

        // Non-jQuery functions (can be global)
        window.exportSettings = function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Info', 'Export functionality coming soon!', 'info');
            }
        };
        
        window.importSettings = function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Info', 'Import functionality coming soon!', 'info');
            }
        };
        
        // Form submission handler
        $('#systemSettingForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const settingId = $('#setting_id').val();
            const url = settingId 
                ? '{{ route("admin.settings.system.update", ":id") }}'.replace(':id', settingId)
                : '{{ route("admin.settings.system.store") }}';
            const method = settingId ? 'PUT' : 'POST';
            
            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if(response.success) {
                        $('#systemSettingModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMsg = xhr.responseJSON?.message || 'Error saving setting';
                    if(Object.keys(errors).length > 0) {
                        errorMsg = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire('Error!', errorMsg, 'error');
                }
            });
        });
    }
    
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit more for jQuery to be fully loaded
            setTimeout(initSystemSettings, 200);
        });
    } else {
        // Wait a bit more for jQuery to be fully loaded
        setTimeout(initSystemSettings, 200);
    }
})();

// Fallback: Ensure functions exist even if jQuery isn't loaded yet
// These will be overwritten by the initSystemSettings function
window.editSystemSetting = function(id) {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.viewSystemSetting = function(id) {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.deleteSystemSetting = function(id) {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.updateQuickSetting = function(key, type, value) {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.bulkDeleteSettings = function() {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.openSystemSettingModal = function() {
    console.warn('jQuery not loaded yet. Please wait...');
};
window.exportSettings = function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire('Info', 'Export functionality coming soon!', 'info');
    }
};
window.importSettings = function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire('Info', 'Import functionality coming soon!', 'info');
    }
};
</script>



