# PDF Standardization Guide

All PDF documents in the system should use the standardized header and footer components.

## Header Component

**Location:** `resources/views/components/pdf-header.blade.php`

**Usage:**
```blade
@include('components.pdf-header', [
    'documentTitle' => 'Your Document Title',
    'documentRef' => 'REF-12345', // Optional
    'documentDate' => '31 Dec 2024' // Optional, defaults to current date
])
```

**Features:**
- Company logo (from database)
- Company name (from OrganizationSetting)
- System name (OfisiLink)
- Contact information (address, phone, email, website, TIN)
- Document title
- Document reference number
- Date (respects timezone from database)

## Footer Component

**Location:** `resources/views/components/pdf-footer.blade.php`

**Usage:**
```blade
@include('components.pdf-footer')
```

**Features:**
- System-generated disclaimer (no signature required)
- Page numbers (Page X of Y)
- Timestamp with timezone
- Powered by EmCa Technologies

## Files to Update

The following PDF files need to be updated to use the standardized format:

1. ✅ `modules/hr/pdf/payslip.blade.php` - **DONE**
2. `modules/tasks/pdf-report.blade.php`
3. `modules/hr/assessments-pdf.blade.php`
4. `modules/assessments/pdf.blade.php`
5. `modules/finance/ledger-pdf.blade.php`
6. `modules/finance/imprest-pdf.blade.php`
7. `modules/hr/permissions-pdf.blade.php`
8. Any other PDF views in the system

## Implementation Steps

1. Remove old header code
2. Add `@include('components.pdf-header', [...])` at the top of the body
3. Remove old footer code and signature lines
4. Add `@include('components.pdf-footer')` at the bottom
5. Update page margins to include footer space: `@page { margin: 20px 30px 60px 30px; }`
6. Remove hardcoded company information
7. Ensure all dates use timezone from OrganizationSetting

## Example Template

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Document Title</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 12px; 
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'YOUR-REF-' . $id; // Your reference format
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'Your Document Title',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <!-- Your document content here -->
    
    @include('components.pdf-footer')
</body>
</html>
```

## Notes

- All company information comes from `OrganizationSetting` model
- No hardcoded company details
- Timezone is respected from database settings
- Page numbers are automatically handled by DomPDF
- No signature lines required (all approvals done in system)


