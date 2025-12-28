# PDF Service Documentation

## Overview

The system now includes an advanced, reusable PDF generation structure that can be used across all modules. The PDF service is built on a base class (`BasePdfService`) that provides common functionality, and specific services extend it for module-specific needs.

## Architecture

### BasePdfService
Located at: `app/Services/BasePdfService.php`

This abstract base class provides:
- Reusable utility methods (currency formatting, number to words, date formatting)
- Base CSS styles with modern, professional design
- Header and footer generation
- Company information management
- Color scheme management

### Module-Specific Services
Each module can extend `BasePdfService` to create module-specific PDF generators.

Example: `PettyCashPdfService` extends `BasePdfService` for petty cash vouchers.

## Usage

### Creating a New PDF Service

1. **Create a new service class** extending `BasePdfService`:

```php
<?php

namespace App\Services;

use App\Models\YourModel;

class YourModulePdfService extends BasePdfService
{
    public function __construct()
    {
        parent::__construct();
        $this->documentType = 'YOUR DOCUMENT TYPE';
    }

    public function generateHtml($data, $logoSrc = null): string
    {
        // Your implementation here
        // Use parent methods like:
        // - $this->formatCurrency($amount)
        // - $this->formatDate($date)
        // - $this->escape($text)
        // - $this->generateHeader($logoSrc)
        // - $this->generateFooter($additionalInfo)
        // - $this->getBaseStyles()
    }
}
```

2. **In your controller**, use the service:

```php
use App\Services\YourModulePdfService;
use Barryvdh\DomPDF\Facade\Pdf;

public function generatePdf($id)
{
    $data = YourModel::findOrFail($id);
    
    $pdfService = new YourModulePdfService();
    
    // Get logo
    $logoSrc = null;
    $logoPath = public_path('assets/img/office_link_logo.png');
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    }
    
    // Generate HTML
    $html = $pdfService->generateHtml($data, $logoSrc);
    
    // Generate PDF
    $pdf = Pdf::loadHtml($html);
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOption('enable-local-file-access', true);
    
    $filename = 'Your_Document_' . $id . '_' . date('Y-m-d') . '.pdf';
    
    return $pdf->stream($filename);
}
```

## Available Methods

### Utility Methods

- `formatCurrency($amount, $currency = 'TZS')` - Format currency with thousand separators
- `convertNumberToWords($number)` - Convert number to words (e.g., "One Thousand")
- `formatDate($date, $format = 'l, F d, Y')` - Format date
- `formatDateTime($datetime, $format = 'F j, Y \a\t g:i A')` - Format datetime
- `escape($text)` - Escape HTML special characters
- `getStatusText($status)` - Convert status to readable text
- `getStatusBadgeClass($status)` - Get CSS class for status badge

### Styling Methods

- `getBaseStyles()` - Get complete CSS stylesheet
- `generateHeader($logoSrc, $documentTitle)` - Generate header HTML
- `generateFooter($additionalInfo)` - Generate footer HTML

### Configuration Methods

- `setCompanyInfo($name, $address, $phone, $email)` - Set company information
- `setDocumentType($type)` - Set document type
- `setColors($mainColor, $secondaryColor)` - Set color scheme

## CSS Classes Available

The base styles provide these CSS classes:

### Layout Classes
- `.pdf-header` - Document header
- `.pdf-section` - Content section
- `.pdf-section-title` - Section title bar
- `.pdf-footer` - Document footer

### Table Classes
- `.pdf-table` - Standard data table
- `.pdf-info-table` - Information table (key-value pairs)
- `.pdf-workflow-table` - Workflow timeline table

### Component Classes
- `.pdf-highlight-box` - Highlighted amount/value box
- `.pdf-comment-box` - Comment/note box
- `.pdf-summary-box` - Summary information box
- `.pdf-status-badge` - Status badge (with variants: pending, approved, completed, rejected)

### Utility Classes
- `.text-center`, `.text-right`, `.text-left` - Text alignment
- `.text-bold`, `.text-italic` - Text styling
- `.mb-1`, `.mb-2`, `.mb-3` - Margin bottom
- `.mt-1`, `.mt-2`, `.mt-3` - Margin top
- `.amount` - Right-aligned currency formatting

## Example: Petty Cash PDF

The `PettyCashPdfService` demonstrates a complete implementation:

1. **Extends BasePdfService**
2. **Uses helper methods** for data formatting
3. **Builds custom sections** (workflow timeline, comments, summary)
4. **Uses base CSS classes** for consistent styling

## Customization

### Changing Colors

```php
$pdfService = new YourModulePdfService();
$pdfService->setColors('#940000', '#a80000'); // Main and secondary colors
```

### Changing Company Info

```php
$pdfService->setCompanyInfo(
    'Your Company Name',
    'Your Address',
    '+255 XXX XXX XXX',
    'info@yourcompany.com'
);
```

### Custom Document Type

```php
$pdfService->setDocumentType('INVOICE');
```

## Best Practices

1. **Always extend BasePdfService** for consistency
2. **Use provided utility methods** instead of reinventing
3. **Use base CSS classes** for consistent styling
4. **Escape all user input** using `$this->escape()`
5. **Handle missing data gracefully** (use null coalescing)
6. **Test PDF generation** with various data scenarios
7. **Keep HTML structure clean** and semantic

## File Structure

```
app/
  Services/
    BasePdfService.php          # Base class
    PettyCashPdfService.php    # Petty cash implementation
    YourModulePdfService.php    # Your module implementation
```

## Route Example

```php
// routes/web.php
Route::get('/your-module/{id}/pdf', [YourController::class, 'generatePdf'])->name('your-module.pdf');
```

## Notes

- The PDF uses **DejaVu Sans** font (included with DomPDF) for better Unicode support
- All colors use the system primary color (#940000) by default
- The watermark text is configurable via `$watermarkText` property
- Page breaks are handled automatically for sections
- Tables are responsive and handle long content gracefully

## Support

For questions or issues with PDF generation, refer to:
- DomPDF documentation: https://github.com/dompdf/dompdf
- Base service code: `app/Services/BasePdfService.php`
- Example implementation: `app/Services/PettyCashPdfService.php`



