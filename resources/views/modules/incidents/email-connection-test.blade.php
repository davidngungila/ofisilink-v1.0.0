@extends('layouts.app')

@section('title', 'Email Connection Testing - Incident Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .email-config-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .email-config-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .status-connected {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    .status-disconnected {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    .status-failed {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        color: white;
    }
    .status-checking {
        background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
        color: white;
    }
    .test-button {
        position: relative;
        overflow: hidden;
    }
    .test-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    .test-button:active::before {
        width: 300px;
        height: 300px;
    }
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .test-result-card {
        border-left: 4px solid #007bff;
        margin-top: 15px;
    }
    .test-result-success {
        border-left-color: #28a745;
    }
    .test-result-error {
        border-left-color: #dc3545;
    }
    
    /* Ensure modals and popups appear in front */
    .modal {
        z-index: 100000 !important;
    }
    .modal-backdrop {
        z-index: 99999 !important;
    }
    .modal-dialog {
        z-index: 100001 !important;
    }
    .swal2-container {
        z-index: 1000000 !important;
    }
    .swal2-popup {
        z-index: 1000001 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- IMAP Extension Check Alert -->
    @php
        $imapAvailable = function_exists('imap_open');
    @endphp
    @if(!$imapAvailable)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bx bx-error-circle me-2"></i>IMAP Extension Not Installed
                </h5>
                <p class="mb-2"><strong>Error:</strong> The PHP IMAP extension is not installed or enabled on your server.</p>
                <hr>
                <p class="mb-2"><strong>Installation Instructions:</strong></p>
                <ul class="mb-2">
                    <li><strong>Windows:</strong> Edit your <code>php.ini</code> file and uncomment the line <code>extension=imap</code>, then restart your web server.</li>
                    <li><strong>Ubuntu/Debian:</strong> Run <code>sudo apt-get install php-imap</code> then restart your web server.</li>
                    <li><strong>CentOS/RHEL:</strong> Run <code>sudo yum install php-imap</code> then restart your web server.</li>
                    <li><strong>macOS (Homebrew):</strong> Run <code>brew install php-imap</code> then restart your web server.</li>
                </ul>
                <p class="mb-0"><strong>Verify installation:</strong> Run <code>php -m | grep imap</code> in your terminal. If you see "imap" in the output, the extension is installed.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-network-chart me-2"></i>Email Connection Testing
                            </h3>
                            <p class="mb-0 text-white-50">Test and verify email account connections</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" onclick="testAllConnections()">
                                <i class="bx bx-refresh me-1"></i>Test All Connections
                            </button>
                            <a href="{{ route('modules.incidents.email.config') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Email Config
                            </a>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light">
                                <i class="bx bx-home me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bx bx-envelope fs-1 text-primary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->count() }}</h3>
                    <small class="text-muted fw-semibold">Total Configurations</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bx bx-check-circle fs-1 text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'connected')->count() }}</h3>
                    <small class="text-muted fw-semibold">Connected</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bx bx-error-circle fs-1 text-danger"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'failed')->count() }}</h3>
                    <small class="text-muted fw-semibold">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bx bx-power-off fs-1 text-info"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('is_active', true)->count() }}</h3>
                    <small class="text-muted fw-semibold">Active</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Configurations for Testing -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm email-config-card">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-network-chart me-2 text-primary"></i>Test Email Connections
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($configs as $config)
                    <div class="card mb-3 test-result-card" id="test-card-{{ $config->id }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $config->email_address }}</h6>
                                            <small class="text-muted">
                                                <span class="badge bg-info">{{ strtoupper($config->protocol) }}</span>
                                                @if($config->ssl_enabled)
                                                <span class="badge bg-success ms-1">SSL</span>
                                                @else
                                                <span class="badge bg-warning ms-1">TLS</span>
                                                @endif
                                                <code class="ms-2">{{ $config->host }}:{{ $config->port }}</code>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    @php
                                    $status = $config->connection_status ?? 'unknown';
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        @if($status === 'connected')
                                        <span class="status-badge status-connected">
                                            <i class="bx bx-check-circle me-1"></i>Connected
                                        </span>
                                        @elseif($status === 'disconnected')
                                        <span class="status-badge status-disconnected">
                                            <i class="bx bx-wifi me-1"></i>Disconnected
                                        </span>
                                        @elseif($status === 'failed')
                                        <span class="status-badge status-failed">
                                            <i class="bx bx-error-circle me-1"></i>Failed
                                        </span>
                                        @else
                                        <span class="status-badge status-checking">
                                            <i class="bx bx-question-mark me-1"></i>Unknown
                                        </span>
                                        @endif
                                    </div>
                                    @if($config->last_connection_test_at)
                                    <small class="text-muted d-block mt-1">
                                        Last tested: {{ $config->last_connection_test_at->format('M j, Y g:i A') }}
                                    </small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    @if($config->connection_error)
                                    <div class="alert alert-danger mb-0 py-2" style="font-size: 0.85rem;">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ strlen($config->connection_error) > 80 ? substr($config->connection_error, 0, 80) . '...' : $config->connection_error }}
                                    </div>
                                    @else
                                    <div class="text-success">
                                        <i class="bx bx-check-circle me-1"></i>No errors
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-primary test-button" 
                                            onclick="testConnection({{ $config->id }})"
                                            id="test-btn-{{ $config->id }}">
                                        <i class="bx bx-refresh me-1"></i>Test
                                    </button>
                                </div>
                            </div>
                            <div id="test-result-{{ $config->id }}" class="mt-3" style="display: none;"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-inbox fs-1"></i>
                        <p class="mt-2 mb-0">No email configurations found.</p>
                        <a href="{{ route('modules.incidents.email.config') }}" class="btn btn-primary mt-3">
                            <i class="bx bx-plus me-1"></i>Add Email Account
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Ensure all modals and popups appear in front
    (function() {
        // Fix modal z-index
        document.addEventListener('show.bs.modal', function(e) {
            e.target.style.zIndex = '100000';
        });
        document.addEventListener('shown.bs.modal', function(e) {
            e.target.style.zIndex = '100000';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.style.zIndex = '99999';
        });
        
        // Fix SweetAlert2 z-index
        if (typeof Swal !== 'undefined') {
            const originalFire = Swal.fire;
            Swal.fire = function(...args) {
                const result = originalFire.apply(this, args);
                setTimeout(() => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) swalContainer.style.zIndex = '1000000';
                }, 10);
                return result;
            };
        }
    })();

    function testConnection(configId) {
        const btn = document.getElementById(`test-btn-${configId}`);
        const resultDiv = document.getElementById(`test-result-${configId}`);
        const testCard = document.getElementById(`test-card-${configId}`);
        
        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Testing...';
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="bx bx-loader-alt bx-spin me-2"></i>Testing connection...</div>';
        
        fetch(`/modules/incidents/email-config/${configId}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-refresh me-1"></i>Test';
            
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success test-result-success">
                        <i class="bx bx-check-circle me-2"></i><strong>Connection Successful!</strong>
                        <p class="mb-0 mt-2">${data.message || 'Connection test passed successfully.'}</p>
                    </div>
                `;
                testCard.classList.add('test-result-success');
                testCard.classList.remove('test-result-error');
                
                // Reload page after 2 seconds to show updated status
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                let errorMessage = data.message || data.error || 'Connection test failed. Please check your settings.';
                
                // Format IMAP extension error with better styling
                let errorHtml = errorMessage;
                if (errorMessage.includes('IMAP extension')) {
                    errorHtml = `
                        <strong>IMAP Extension Not Installed</strong>
                        <p class="mb-2 mt-2">${errorMessage}</p>
                        <div class="bg-light p-3 rounded mt-2">
                            <small><strong>Quick Fix:</strong></small>
                            <ul class="mb-0 small">
                                <li>Windows: Edit php.ini and uncomment <code>extension=imap</code></li>
                                <li>Ubuntu/Debian: <code>sudo apt-get install php-imap</code></li>
                                <li>CentOS/RHEL: <code>sudo yum install php-imap</code></li>
                            </ul>
                            <p class="mb-0 mt-2 small">After installation, restart your web server and verify with: <code>php -m | grep imap</code></p>
                        </div>
                    `;
                } else {
                    errorHtml = `<p class="mb-0 mt-2">${errorMessage}</p>`;
                }
                
                resultDiv.innerHTML = `
                    <div class="alert alert-danger test-result-error">
                        <i class="bx bx-error-circle me-2"></i><strong>Connection Failed!</strong>
                        <div class="mt-2">${errorHtml}</div>
                    </div>
                `;
                testCard.classList.add('test-result-error');
                testCard.classList.remove('test-result-success');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-refresh me-1"></i>Test';
            resultDiv.innerHTML = `
                <div class="alert alert-danger test-result-error">
                    <i class="bx bx-error-circle me-2"></i><strong>Error!</strong>
                    <p class="mb-0 mt-2">An error occurred while testing the connection: ${error.message}</p>
                </div>
            `;
            testCard.classList.add('test-result-error');
            testCard.classList.remove('test-result-success');
        });
    }

    function testAllConnections() {
        Swal.fire({
            title: 'Testing All Connections',
            text: 'This may take a few moments...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const configs = @json($configs->pluck('id'));
        let completed = 0;
        let successful = 0;
        let failed = 0;

        configs.forEach(configId => {
            fetch(`/modules/incidents/email-config/${configId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                completed++;
                if (data.success) {
                    successful++;
                } else {
                    failed++;
                }

                if (completed === configs.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Testing Complete',
                        html: `
                            <p>Total: ${configs.length}</p>
                            <p class="text-success">Successful: ${successful}</p>
                            <p class="text-danger">Failed: ${failed}</p>
                        `,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                completed++;
                failed++;
                if (completed === configs.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Testing Complete',
                        html: `
                            <p>Total: ${configs.length}</p>
                            <p class="text-success">Successful: ${successful}</p>
                            <p class="text-danger">Failed: ${failed}</p>
                        `,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        });
    }
</script>
@endpush
@endsection




