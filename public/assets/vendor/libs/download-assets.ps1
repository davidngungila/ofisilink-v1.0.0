# PowerShell script to download required assets
# Run this script from the ofisi/public/assets/vendor/libs directory

$ErrorActionPreference = "Stop"

# Create directories if they don't exist
$directories = @("chart.js", "sweetalert2")
foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "Created directory: $dir" -ForegroundColor Green
    }
}

# Download Chart.js
Write-Host "Downloading Chart.js..." -ForegroundColor Yellow
$chartJsUrl = "https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
$chartJsPath = "chart.js\chart.umd.min.js"
try {
    Invoke-WebRequest -Uri $chartJsUrl -OutFile $chartJsPath -UseBasicParsing
    Write-Host "✓ Chart.js downloaded successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to download Chart.js: $_" -ForegroundColor Red
    exit 1
}

# Download SweetAlert2 JS
Write-Host "Downloading SweetAlert2 JS..." -ForegroundColor Yellow
$swalJsUrl = "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"
$swalJsPath = "sweetalert2\sweetalert2.min.js"
try {
    Invoke-WebRequest -Uri $swalJsUrl -OutFile $swalJsPath -UseBasicParsing
    Write-Host "✓ SweetAlert2 JS downloaded successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to download SweetAlert2 JS: $_" -ForegroundColor Red
    exit 1
}

# Download SweetAlert2 CSS
Write-Host "Downloading SweetAlert2 CSS..." -ForegroundColor Yellow
$swalCssUrl = "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"
$swalCssPath = "sweetalert2\sweetalert2.min.css"
try {
    Invoke-WebRequest -Uri $swalCssUrl -OutFile $swalCssPath -UseBasicParsing
    Write-Host "✓ SweetAlert2 CSS downloaded successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to download SweetAlert2 CSS: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`nAll assets downloaded successfully!" -ForegroundColor Green
Write-Host "Files location:" -ForegroundColor Cyan
Write-Host "  - chart.js/chart.umd.min.js" -ForegroundColor White
Write-Host "  - sweetalert2/sweetalert2.min.js" -ForegroundColor White
Write-Host "  - sweetalert2/sweetalert2.min.css" -ForegroundColor White









