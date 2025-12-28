<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Barcodes - {{ now()->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background: white;
        }
        .barcode-label {
            width: 3.5in;
            height: 1.5in;
            border-radius: 8px;
            overflow: hidden;
            margin: 10px;
            background: white;
            display: inline-block;
            vertical-align: top;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
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
            justify-content: flex-start;
        }
        @page {
            margin: 0.5in;
        }
    </style>
</head>
<body>
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
</body>
</html>
