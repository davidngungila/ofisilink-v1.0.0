@extends('layouts.app')

@section('title', 'Print Asset Barcodes')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Print Asset Barcodes</h4>
</div>
@endsection

@push('styles')
<style>
    @media print {
        body { margin: 0; padding: 0; background: white; }
        .no-print { display: none; }
        .barcode-label {
            page-break-inside: avoid;
            margin: 10px;
        }
    }
    body {
        background: #f0f0f0;
    }
    .barcode-label {
        width: 3.5in;
        height: 1.5in;
        border-radius: 8px;
        overflow: hidden;
        margin: 10px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: inline-block;
        vertical-align: top;
    }
    .barcode-label.compact {
        width: 3in;
        height: 1.2in;
    }
    .barcode-header {
        background: #1a237e;
        color: white;
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        letter-spacing: 0.5px;
    }
    .barcode-label.compact .barcode-header {
        padding: 6px 10px;
        font-size: 10px;
    }
    .barcode-body {
        padding: 15px 12px;
        text-align: center;
        background: white;
    }
    .barcode-label.compact .barcode-body {
        padding: 12px 10px;
    }
    .barcode-image-container {
        margin: 12px 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 55px;
    }
    .barcode-label.compact .barcode-image-container {
        min-height: 45px;
        margin: 10px 0;
    }
    .barcode-image-container img {
        max-width: 100%;
        height: auto;
        max-height: 65px;
        image-rendering: crisp-edges;
    }
    .barcode-label.compact .barcode-image-container img {
        max-height: 50px;
    }
    .barcode-box {
        border: 1px solid #000;
        padding: 10px 15px;
        margin: 0 auto;
        display: inline-block;
        background: white;
        min-width: 180px;
    }
    .barcode-label.compact .barcode-box {
        padding: 8px 12px;
        min-width: 150px;
    }
    .barcode-box-text {
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #888;
        letter-spacing: 2px;
        font-weight: normal;
    }
    .barcode-label.compact .barcode-box-text {
        font-size: 12px;
        letter-spacing: 1.5px;
    }
    .barcode-number {
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 3px;
        margin-top: 8px;
        color: #000;
        font-family: Arial, sans-serif;
    }
    .barcode-label.compact .barcode-number {
        font-size: 16px;
        letter-spacing: 2px;
        margin-top: 6px;
    }
    .barcodes-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
    }
</style>
@endpush

@section('content')
<div class="no-print">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Print Asset Barcodes</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Label Type</label>
                    <select class="form-control" id="labelType" onchange="updateLabels()">
                        <option value="standard" {{ $labelType === 'standard' ? 'selected' : '' }}>Standard (3.5" x 1.5")</option>
                        <option value="compact" {{ $labelType === 'compact' ? 'selected' : '' }}>Compact (3" x 1.2")</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>&nbsp;</label>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary">
                            Print All
                        </button>
                        <a href="{{ route('modules.accounting.fixed-assets.print-barcodes', ['export' => 'pdf', 'label_type' => $labelType]) }}" class="btn btn-success">
                            Export PDF
                        </a>
                        <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                            Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Total Assets:</strong> {{ $assets->count() }} | 
                <strong>Label Type:</strong> {{ ucfirst($labelType) }}
            </div>
        </div>
    </div>
</div>

@php
    $orgSettings = \App\Models\OrganizationSetting::getSettings();
    $companyName = $orgSettings->company_name ?? config('app.name', 'ABC Company');
@endphp
<div class="barcodes-container">
    @foreach($assets as $asset)
    <div class="barcode-label {{ $labelType }}">
        <div class="barcode-header">
            Property of {{ $companyName }}
        </div>
        <div class="barcode-body">
            <div class="barcode-image-container">
                @php
                    $barcodeData = \App\Http\Controllers\FixedAssetController::generateBarcodeImage($asset->barcode_number);
                @endphp
                @if($barcodeData)
                    <img src="data:image/png;base64,{{ $barcodeData }}" alt="Barcode {{ $asset->barcode_number }}">
                @else
                    <div class="barcode-box">
                        <div class="barcode-box-text">{{ $asset->barcode_number }}</div>
                    </div>
                @endif
            </div>
            <div class="barcode-number">{{ $asset->barcode_number }}</div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
function updateLabels() {
    const labelType = document.getElementById('labelType').value;
    window.location.href = '{{ route("modules.accounting.fixed-assets.print-barcodes") }}?label_type=' + labelType;
}
</script>
@endpush
@endsection
