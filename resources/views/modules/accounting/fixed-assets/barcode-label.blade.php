<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Barcode - {{ $asset->barcode_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f0f0f0;
        }
        .barcode-container {
            width: 3.5in;
            height: 1.5in;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px auto;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .barcode-header {
            background: #1a237e;
            color: white;
            padding: 10px 15px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .barcode-body {
            padding: 20px 15px;
            text-align: center;
            background: white;
        }
        .barcode-image-container {
            margin: 15px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60px;
        }
        .barcode-image-container img {
            max-width: 100%;
            height: auto;
            max-height: 70px;
            image-rendering: crisp-edges;
        }
        .barcode-box {
            border: 1px solid #000;
            padding: 12px 20px;
            margin: 10px auto;
            display: inline-block;
            background: white;
            min-width: 200px;
        }
        .barcode-box-text {
            font-family: Arial, sans-serif;
            font-size: 16px;
            color: #888;
            letter-spacing: 2px;
            font-weight: normal;
        }
        .barcode-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-top: 10px;
            color: #000;
            font-family: Arial, sans-serif;
        }
        .print-actions {
            text-align: center;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px;">
            Print Barcode
        </button>
        <a href="{{ route('modules.accounting.fixed-assets.index') }}" style="padding: 10px 20px; font-size: 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 10px;">
            Back to Assets
        </a>
    </div>

    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $companyName = $orgSettings->company_name ?? config('app.name', 'ABC Company');
    @endphp
    <div class="barcode-container">
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
</body>
</html>
