@extends('layouts.app')

@section('title', 'Scan Barcode')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Scan Barcode</h4>
    <ul class="db-breadcrumb-list">
        <li><a href="{{ route('modules.accounting.index') }}"><i class="fa fa-home"></i>Accounting</a></li>
        <li><a href="{{ route('modules.accounting.fixed-assets.index') }}">Fixed Assets</a></li>
        <li>Scan Barcode</li>
    </ul>
</div>
@endsection

@push('styles')
<style>
    .barcode-scanner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    @media (max-width: 768px) {
        .barcode-scanner {
            padding: 20px;
        }
    }
    .info-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
        height: 100%;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 1rem;
        color: #212529;
    }
    .barcode-display {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .scan-result-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 0;
    }
    .asset-name-display {
        font-size: 1.5rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #dee2e6;
    }
    .quick-stats {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-box {
        flex: 1;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }
    .stat-box.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .stat-box.warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }
    .stat-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }
    .stat-value {
        font-size: 1.3rem;
        font-weight: bold;
    }
    .mobile-scan-hint {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        font-size: 0.9rem;
    }
    .mobile-scan-hint i {
        margin-right: 8px;
    }
    #barcodeInput {
        font-size: 18px;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.95);
        color: #212529;
    }
    #barcodeInput:focus {
        border-color: #fff;
        background: #fff;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.5);
    }
    @media (max-width: 768px) {
        #barcodeInput {
            font-size: 16px;
            padding: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="barcode-scanner">
            <h4 class="mb-3">
                <i class="fas fa-barcode"></i> Barcode Scanner
            </h4>
            <form method="GET" action="{{ route('modules.accounting.fixed-assets.scan-barcode') }}" id="scanForm">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" 
                               name="barcode" 
                               id="barcodeInput" 
                               class="form-control form-control-lg" 
                               placeholder="Scan or enter barcode number..." 
                               value="{{ $barcodeNumber ?? '' }}"
                               autofocus
                               autocomplete="off"
                               inputmode="text"
                               autocapitalize="off"
                               autocorrect="off"
                               spellcheck="false">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-light btn-lg btn-block">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="mobile-scan-hint">
                    <i class="fas fa-mobile-alt"></i> 
                    <strong>Mobile Scanner:</strong> Open your barcode scanner app, scan the code, and it will automatically fill and search.
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($error))
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ $error }}
        </div>
        <div class="text-center">
            <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                Back to Assets
            </a>
        </div>
    </div>
</div>
@elseif(isset($asset))
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-check-circle"></i> Asset Found - Barcode: <strong>{{ $asset->barcode_number }}</strong>
                </h4>
            </div>
            <div class="card-body">
                <h3 class="mb-4" style="color: #212529; font-weight: 600; border-bottom: 3px solid #007bff; padding-bottom: 10px;">
                    Asset Information
                </h3>
                
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless" style="font-size: 1rem;">
                            <tbody>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Asset Code:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->asset_code }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Category:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Serial Number:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->serial_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Manufacturer:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->manufacturer ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Model:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->model ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Location:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Department:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Assigned To:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->assignedUser->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Status:</td>
                                    <td style="padding: 12px 15px; vertical-align: top;">
                                        <span class="badge badge-{{ $asset->status == 'Active' ? 'success' : ($asset->status == 'Disposed' ? 'danger' : 'warning') }}" style="font-size: 0.9rem; padding: 6px 12px;">
                                            {{ $asset->status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Purchase Date:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->purchase_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Vendor:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 200px; font-weight: 600; color: #495057; padding: 12px 15px; vertical-align: top;">Invoice Number:</td>
                                    <td style="padding: 12px 15px; vertical-align: top; font-weight: 500; color: #212529;">{{ $asset->invoice_number ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr style="margin: 30px 0; border-top: 2px solid #dee2e6;">

                <div class="row mt-4">
                    <div class="col-md-12 text-center mb-4">
                        <h5 class="mb-3" style="color: #495057; font-weight: 600;">Barcode Display</h5>
                        <div class="barcode-display" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                            @php
                                $barcodeData = \App\Http\Controllers\FixedAssetController::generateBarcodeImage($asset->barcode_number);
                            @endphp
                            @if($barcodeData)
                                <img src="data:image/png;base64,{{ $barcodeData }}" alt="Barcode {{ $asset->barcode_number }}" style="max-width: 100%; height: auto; max-height: 120px; margin-bottom: 15px;">
                            @endif
                            <div>
                                <code style="font-size: 1.3rem; font-weight: bold; letter-spacing: 3px; color: #212529; background: white; padding: 10px 20px; border-radius: 4px; border: 1px solid #dee2e6;">{{ $asset->barcode_number }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="margin: 30px 0; border-top: 2px solid #dee2e6;">

                <div class="text-center mt-4">
                    <a href="{{ route('modules.accounting.fixed-assets.show', $asset->id) }}" class="btn btn-primary btn-lg mr-2">
                        View Full Details
                    </a>
                    <a href="{{ route('modules.accounting.fixed-assets.print-barcode', $asset->id) }}" class="btn btn-info btn-lg mr-2" target="_blank">
                        Print Barcode
                    </a>
                    <a href="{{ route('modules.accounting.fixed-assets.edit', $asset->id) }}" class="btn btn-warning btn-lg mr-2">
                        Edit Asset
                    </a>
                    <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary btn-lg mr-2">
                        Back to Assets
                    </a>
                    <button onclick="document.getElementById('barcodeInput').focus(); document.getElementById('barcodeInput').select(); document.getElementById('barcodeInput').value='';" class="btn btn-success btn-lg">
                        Scan Another
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-barcode fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Scan or enter a barcode to view asset information</h5>
                <p class="text-muted">Use a barcode scanner or manually enter the barcode number above</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var scanTimeout;
    var lastScanValue = '';
    var scanStartTime = 0;
    
    // Auto-submit on Enter key (for keyboard input and scanner apps)
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            submitScan();
        }
    });
    
    // Handle rapid input from barcode scanners
    // Scanners typically send data very quickly, so we detect this pattern
    $('#barcodeInput').on('input', function() {
        var currentValue = $(this).val();
        var currentTime = Date.now();
        
        // Clear any existing timeout
        clearTimeout(scanTimeout);
        
        // If input is coming in very fast (typical of scanners), wait a bit then submit
        if (currentTime - scanStartTime < 100) {
            // Rapid input detected - likely from scanner
            scanTimeout = setTimeout(function() {
                if ($('#barcodeInput').val().length > 0) {
                    submitScan();
                }
            }, 300); // Wait 300ms after last character
        } else {
            // Normal typing - wait longer before auto-submit
            scanTimeout = setTimeout(function() {
                if ($('#barcodeInput').val().length > 0 && $('#barcodeInput').val() === currentValue) {
                    // Only auto-submit if value hasn't changed (user stopped typing)
                    // This prevents auto-submit while user is still typing
                }
            }, 2000);
        }
        
        scanStartTime = currentTime;
        lastScanValue = currentValue;
    });
    
    // Handle paste events (some scanner apps paste the barcode)
    $('#barcodeInput').on('paste', function(e) {
        setTimeout(function() {
            submitScan();
        }, 100);
    });
    
    // Submit scan function
    function submitScan() {
        var barcodeValue = $('#barcodeInput').val().trim();
        if (barcodeValue.length > 0) {
            $('#scanForm').submit();
        }
    }
    
    // Focus input on page load
    $('#barcodeInput').focus();
    
    // Select all text when focused (for easy clearing)
    $('#barcodeInput').on('focus', function() {
        $(this).select();
    });
    
    // Handle visibility change (when user returns to tab/app)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Page became visible - focus input for next scan
            setTimeout(function() {
                $('#barcodeInput').focus();
                $('#barcodeInput').select();
            }, 100);
        }
    });
    
    // Handle focus events
    $(window).on('focus', function() {
        setTimeout(function() {
            $('#barcodeInput').focus();
        }, 100);
    });
    
    // Prevent form submission on Enter if input is empty
    $('#scanForm').on('submit', function(e) {
        var barcodeValue = $('#barcodeInput').val().trim();
        if (barcodeValue.length === 0) {
            e.preventDefault();
            $('#barcodeInput').focus();
            return false;
        }
    });
    
    // Clear input after successful scan (when page reloads with results)
    @if(isset($asset))
        // After showing results, prepare for next scan
        setTimeout(function() {
            $('#barcodeInput').val('');
            $('#barcodeInput').focus();
        }, 500);
    @endif
});
</script>
@endpush

