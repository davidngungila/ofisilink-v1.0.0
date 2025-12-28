# Global PDF Structure Documentation

## Overview

The system now uses a **standardized Blade template structure** for all PDF generation, following the format established in `payslip.blade.php`. This ensures consistency, maintainability, and reusability across all modules.

## Structure

### 1. Blade Templates
All PDFs are generated using Blade templates located in:
- `resources/views/modules/{module}/pdf/{document-name}.blade.php`

Example: `resources/views/modules/finance/pdf/petty-cash.blade.php`

### 2. Reusable Components
Standard components are located in `resources/views/components/`:

- **`pdf-header.blade.php`** - Company header with logo, name, address, and document title
- **`pdf-footer.blade.php`** - Footer with page numbers, generation timestamp, and system info
- **`pdf-disclaimer.blade.php`** - Standard disclaimer text

### 3. Service Classes
PDF services extend `BasePdfService` and use Blade templates:

```php
class YourModulePdfService extends BasePdfService
{
    public function generateHtml($data, $logoSrc = null): string
    {
        return view('modules.your-module.pdf.your-document', [
            'data' => $data
        ])->render();
    }
}
```

## Standard Template Structure

Every PDF template should follow this structure:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Title</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 12px; 
            line-height: 1.4; 
        }
        
        /* Your custom styles here */
        .section { margin-bottom: 20px; }
        .section-title { 
            background-color: #f5f5f5; 
            padding: 8px 12px; 
            font-weight: bold; 
            border-left: 4px solid #940000; 
            margin-bottom: 10px; 
        }
        /* ... more styles ... */
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'YOUR-REF-' . $id;
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'YOUR DOCUMENT TITLE',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <!-- Your document content here -->
    
    @include('components.pdf-disclaimer')
    
    @include('components.pdf-footer')
</body>
</html>
```

## Key Features

### 1. Organization Settings Integration
All PDFs automatically pull company information from `OrganizationSetting`:
- Company name, address, phone, email
- Company logo
- Timezone and date format preferences
- Tax ID and other details

### 2. Consistent Styling
- Primary color: `#940000`
- Standard section titles with left border
- Consistent table styling
- Professional typography

### 3. Standard Components
- **Header**: Automatically includes company logo and info
- **Footer**: Page numbers, generation timestamp, system branding
- **Disclaimer**: Standard legal disclaimer

### 4. Helper Functions
Common helpers available in templates:
- Currency formatting: `$formatCurrency($amount)`
- Number to words: `\App\Services\PayrollPdfService::convertNumberToWords($number)`
- Date formatting: Use Carbon with timezone from settings

## Example: Petty Cash PDF

Location: `resources/views/modules/finance/pdf/petty-cash.blade.php`

This template demonstrates:
- Using `@include` for components
- Organization settings integration
- Custom sections and tables
- Workflow timeline display
- Comments and summary sections

## Creating a New PDF Template

1. **Create the Blade template** in `resources/views/modules/{module}/pdf/{name}.blade.php`

2. **Follow the standard structure**:
   - Include `pdf-header` at the top
   - Add your content sections
   - Include `pdf-disclaimer` before footer
   - Include `pdf-footer` at the bottom

3. **Use standard CSS classes**:
   - `.section` - Content section
   - `.section-title` - Section header
   - `.info-table` - Key-value information table
   - `.salary-table` - Data table with amounts
   - `.summary-breakdown` - Summary information box

4. **Create/Update the service class**:
   ```php
   public function generateHtml($data, $logoSrc = null): string
   {
       return view('modules.your-module.pdf.your-document', [
           'data' => $data
       ])->render();
   }
   ```

5. **Update the controller**:
   ```php
   $pdfService = new YourModulePdfService();
   $html = $pdfService->generateHtml($data);
   $pdf = Pdf::loadHtml($html);
   return $pdf->stream($filename);
   ```

## Benefits

1. **Consistency**: All PDFs look and feel the same
2. **Maintainability**: Update components once, affects all PDFs
3. **Flexibility**: Easy to customize per module while maintaining standards
4. **Organization Settings**: Automatic integration with company data
5. **Timezone Support**: All dates respect organization timezone
6. **Reusability**: Components can be used across all modules

## Migration Guide

To migrate existing PDFs to the new structure:

1. Move HTML generation from service class to Blade template
2. Replace hardcoded company info with `@include('components.pdf-header')`
3. Replace footer code with `@include('components.pdf-footer')`
4. Update service class to use `view()->render()`
5. Remove logo handling from controller (handled by component)
6. Test PDF generation

## Files Updated

- ✅ `modules/finance/pdf/petty-cash.blade.php` - New template
- ✅ `app/Services/PettyCashPdfService.php` - Updated to use Blade
- ✅ `app/Http/Controllers/PettyCashController.php` - Simplified

## Next Steps

Apply this structure to:
- Imprest PDFs
- Ledger PDFs
- Assessment PDFs
- Task reports
- Any other PDF documents in the system

## Support

For questions or issues:
- Check existing templates: `payslip.blade.php`, `petty-cash.blade.php`
- Review components: `resources/views/components/pdf-*.blade.php`
- See BasePdfService: `app/Services/BasePdfService.php`



