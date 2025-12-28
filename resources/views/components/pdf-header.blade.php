@php
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Storage;

$orgSettings = OrganizationSetting::getSettings();
$systemName = config('app.name', 'OfisiLink');

// Get organization info from SystemSetting if available
// Ensure all values are strings, not arrays
$companyName = is_array($orgSettings->company_name ?? null) ? config('app.name', 'Company Name') : (string)($orgSettings->company_name ?? config('app.name', 'Company Name'));
$companyAddress = is_array($orgSettings->company_address ?? null) ? '' : (string)($orgSettings->company_address ?? '');
$companyCity = is_array($orgSettings->company_city ?? null) ? '' : (string)($orgSettings->company_city ?? '');
$companyState = is_array($orgSettings->company_state ?? null) ? '' : (string)($orgSettings->company_state ?? '');
$companyCountry = is_array($orgSettings->company_country ?? null) ? 'Tanzania' : (string)($orgSettings->company_country ?? 'Tanzania');
$companyPostalCode = is_array($orgSettings->company_postal_code ?? null) ? '' : (string)($orgSettings->company_postal_code ?? '');

// Build full address - ensure all components are strings
$addressParts = array_filter([
    $companyAddress,
    $companyCity,
    $companyState,
    $companyPostalCode,
    $companyCountry
], function($part) {
    return !empty($part) && is_string($part);
});
$fullAddress = trim(implode(', ', $addressParts));

$companyPhone = is_array($orgSettings->company_phone ?? null) ? '' : (string)($orgSettings->company_phone ?? '');
$companyEmail = is_array($orgSettings->company_email ?? null) ? '' : (string)($orgSettings->company_email ?? '');
$companyWebsite = is_array($orgSettings->company_website ?? null) ? '' : (string)($orgSettings->company_website ?? '');
$companyTaxId = is_array($orgSettings->company_tax_id ?? null) ? '' : (string)($orgSettings->company_tax_id ?? '');

// Logo path - convert to absolute path for PDF
$logoPath = null;
$logoSrc = null;

// Build list of possible logo paths to check
$possiblePaths = [];

// First, try to get logo from organization settings
if ($orgSettings && $orgSettings->company_logo) {
    $logoPathFromDB = $orgSettings->company_logo;
    
    // Path 1: storage/app/public/{logo_path} (most common - logo stored as 'settings/filename.png')
    $possiblePaths[] = storage_path('app/public/' . $logoPathFromDB);
    
    // Path 2: public/storage/{logo_path} (symlinked)
    $possiblePaths[] = public_path('storage/' . $logoPathFromDB);
    
    // Path 3: If logo_path already includes 'public/', remove it
    if (strpos($logoPathFromDB, 'public/') === 0) {
        $possiblePaths[] = storage_path('app/' . $logoPathFromDB);
        $possiblePaths[] = public_path('storage/' . str_replace('public/', '', $logoPathFromDB));
    }
    
    // Path 4: Direct path if it's already absolute
    if (file_exists($logoPathFromDB) && is_file($logoPathFromDB)) {
        $possiblePaths[] = $logoPathFromDB;
    }
    
    // Path 5: Check using Storage facade (most reliable)
    try {
        if (Storage::disk('public')->exists($logoPathFromDB)) {
            $storagePath = Storage::disk('public')->path($logoPathFromDB);
            if ($storagePath && file_exists($storagePath)) {
                $possiblePaths[] = $storagePath;
            }
        }
    } catch (\Exception $e) {
        // Continue with other paths
    }
    
    // Path 5b: Try to get path using Storage adapter
    try {
        if (Storage::disk('public')->exists($logoPathFromDB)) {
            $realPath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . $logoPathFromDB;
            if (file_exists($realPath)) {
                $possiblePaths[] = $realPath;
            }
        }
    } catch (\Exception $e) {
        // Continue with other paths
    }
    
    // Path 6: Try with 'settings/' prefix if not already present
    if (strpos($logoPathFromDB, 'settings/') !== 0) {
        $possiblePaths[] = storage_path('app/public/settings/' . $logoPathFromDB);
        $possiblePaths[] = public_path('storage/settings/' . $logoPathFromDB);
    }
}

// Fallback: Check for default logo in assets folder
$defaultLogoPaths = [
    public_path('assets/img/office_link_logo.png'),
    public_path('assets/img/logo.png'),
    public_path('assets/img/company-logo.png'),
    public_path('images/logo.png'),
];

$possiblePaths = array_merge($possiblePaths, $defaultLogoPaths);

// Remove duplicates and null values
$possiblePaths = array_filter(array_unique($possiblePaths));

// Find the first existing logo file
foreach ($possiblePaths as $path) {
    if ($path && file_exists($path) && is_file($path) && is_readable($path)) {
        // Verify it's actually an image file
        $imageInfo = @getimagesize($path);
        if ($imageInfo !== false) {
            $logoPath = $path;
            break;
        }
    }
}

// Convert to base64 for PDF compatibility
if ($logoPath) {
    try {
        $logoData = file_get_contents($logoPath);
        if ($logoData !== false) {
            $logoBase64 = base64_encode($logoData);
            $logoExtension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            
            // Map extensions to MIME types
            $mimeTypes = [
                'jpg' => 'jpeg',
                'jpeg' => 'jpeg',
                'png' => 'png',
                'gif' => 'gif',
                'webp' => 'webp',
                'svg' => 'svg+xml'
            ];
            
            $mimeType = $mimeTypes[$logoExtension] ?? 'jpeg';
            $logoSrc = 'data:image/' . $mimeType . ';base64,' . $logoBase64;
        }
    } catch (\Exception $e) {
        // If logo loading fails, log and continue without logo
        \Log::warning('Failed to load logo for PDF: ' . $e->getMessage(), [
            'logo_path' => $logoPath,
            'error' => $e->getMessage()
        ]);
        $logoSrc = null;
    }
}

// Get document details - ensure all are strings
$documentTitle = is_array($documentTitle ?? null) ? 'Document' : (string)($documentTitle ?? 'Document');
$documentRef = is_array($documentRef ?? null) ? null : ($documentRef ?? null);
if ($documentRef !== null && !is_string($documentRef)) {
    $documentRef = (string)$documentRef;
}

// Safely get timezone and date format
$headerTimezone = $orgSettings->timezone ?? config('app.timezone');
if (is_array($headerTimezone)) {
    $headerTimezone = config('app.timezone', 'Africa/Dar_es_Salaam');
} else {
    $headerTimezone = (string)$headerTimezone;
}

$headerDateFormat = $orgSettings->date_format ?? 'd M Y';
if (is_array($headerDateFormat)) {
    $headerDateFormat = 'd M Y';
} else {
    $headerDateFormat = (string)$headerDateFormat;
}

$documentDate = is_array($documentDate ?? null) ? now()->setTimezone($headerTimezone)->format($headerDateFormat) : ($documentDate ?? now()->setTimezone($headerTimezone)->format($headerDateFormat));

// Role-based buttons for physical and digital files
$showButtons = isset($showActionButtons) && $showActionButtons === true;
$fileType = $fileType ?? null; // 'physical' or 'digital'
$fileId = $fileId ?? null;
$isFirstPage = isset($isFirstPage) ? (bool)$isFirstPage : true;

// Get authenticated user and their roles
$user = auth()->user();
$canManageFiles = false;
$isSystemAdmin = false;
$isHOD = false;
$isHROfficer = false;
$isCEO = false;
$isAccountant = false;
$isStaff = false;

if ($user && method_exists($user, 'hasRole')) {
    try {
        $isSystemAdmin = $user->hasRole('System Admin');
        $isHOD = $user->hasRole('HOD');
        $isHROfficer = $user->hasRole('HR Officer');
        $isCEO = $user->hasRole('CEO') || $user->hasRole('Director');
        $isAccountant = $user->hasRole('Accountant');
        $isStaff = $user->hasRole('Staff');
        
        // Users who can manage files: System Admin, HOD, HR Officer
        $canManageFiles = $isSystemAdmin || $isHOD || $isHROfficer;
    } catch (\Exception $e) {
        // If role checking fails, set all to false (safe default)
        // Silently fail to prevent breaking PDF generation
    }
}
@endphp

<div class="pdf-header" style="border-bottom: 3px solid #940000; padding-bottom: 15px; margin-bottom: 20px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 20%; vertical-align: top; text-align: left;">
                @if(isset($logoSrc) && $logoSrc)
                <img src="{{ $logoSrc }}" alt="Company Logo" style="max-width: 120px; max-height: 120px; height: auto; width: auto;">
                @else
                <div style="width: 100px; height: 100px; background-color: #f0f0f0; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999;">
                    No Logo
                </div>
                @endif
            </td>
            <td style="width: 60%; vertical-align: top; text-align: center;">
                <h1 style="color: #940000; margin: 0; font-size: 22px; font-weight: bold;">{{ htmlspecialchars($companyName) }}</h1>
                <div style="margin-top: 8px; font-size: 10px; color: #555; line-height: 1.4;">
                    @if($fullAddress)
                    <div>{{ htmlspecialchars($fullAddress) }}</div>
                    @endif
                    <div style="margin-top: 3px;">
                        @if($companyPhone)
                            Phone: {{ htmlspecialchars($companyPhone) }}
                        @endif
                        @if($companyEmail)
                            @if($companyPhone) | @endif
                            Email: {{ htmlspecialchars($companyEmail) }}
                        @endif
                        @if($companyWebsite)
                            @if($companyPhone || $companyEmail) | @endif
                            Website: {{ htmlspecialchars($companyWebsite) }}
                        @endif
                    </div>
                    @if($companyTaxId)
                    <div style="margin-top: 3px;">TIN: {{ htmlspecialchars($companyTaxId) }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 20%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; color: #666;">
                    <div><strong>Date:</strong></div>
                    <div style="margin-top: 3px;">{{ $documentDate }}</div>
                </div>
            </td>
        </tr>
    </table>
    
    <div style="margin-top: 15px; padding: 8px; background-color: #940000; color: white; text-align: center; font-weight: bold; font-size: 14px;">
        {{ htmlspecialchars($documentTitle) }}
        @if($documentRef)
            | Ref: {{ htmlspecialchars($documentRef) }}
        @endif
    </div>
    
    {{-- Role-based action buttons for physical and digital files (first page only) --}}
    @if($showButtons && $isFirstPage && $fileType && $user)
        @php
            $baseUrl = config('app.url');
            $buttons = [];
            
            if ($fileType === 'physical') {
                // Physical file buttons based on roles
                if ($canManageFiles) {
                    // System Admin, HOD, HR Officer - full access
                    if ($fileId) {
                        $buttons[] = [
                            'label' => 'Edit',
                            'url' => $baseUrl . '/files/physical/edit/' . $fileId,
                            'class' => 'btn-outline-warning'
                        ];
                        $buttons[] = [
                            'label' => 'Assign',
                            'url' => $baseUrl . '/files/physical/assign/' . $fileId,
                            'class' => 'btn-outline-info'
                        ];
                    }
                    $buttons[] = [
                        'label' => 'Manage Files',
                        'url' => $baseUrl . '/files/physical/manage',
                        'class' => 'btn-outline-primary'
                    ];
                } elseif ($isStaff) {
                    // Staff - can request files
                    if ($fileId) {
                        $buttons[] = [
                            'label' => 'Request File',
                            'url' => $baseUrl . '/files/physical/request/' . $fileId,
                            'class' => 'btn-outline-success'
                        ];
                    }
                    $buttons[] = [
                        'label' => 'My Requests',
                        'url' => $baseUrl . '/files/physical/my-requests',
                        'class' => 'btn-outline-info'
                    ];
                }
                
                // View button - available to all authenticated users
                if ($fileId) {
                    $buttons[] = [
                        'label' => 'View Details',
                        'url' => $baseUrl . '/files/physical/view/' . $fileId,
                        'class' => 'btn-outline-primary'
                    ];
                }
            } elseif ($fileType === 'digital') {
                // Digital file buttons based on roles
                if ($canManageFiles) {
                    // System Admin, HOD, HR Officer - full access
                    if ($fileId) {
                        $buttons[] = [
                            'label' => 'Edit',
                            'url' => $baseUrl . '/files/digital/edit/' . $fileId,
                            'class' => 'btn-outline-warning'
                        ];
                        $buttons[] = [
                            'label' => 'Delete',
                            'url' => $baseUrl . '/files/digital/delete/' . $fileId,
                            'class' => 'btn-outline-danger'
                        ];
                    }
                    $buttons[] = [
                        'label' => 'Upload File',
                        'url' => $baseUrl . '/files/digital/upload',
                        'class' => 'btn-outline-success'
                    ];
                    $buttons[] = [
                        'label' => 'Create Folder',
                        'url' => $baseUrl . '/files/digital/create-folder',
                        'class' => 'btn-outline-primary'
                    ];
                } elseif ($isStaff) {
                    // Staff - can view and download
                    if ($fileId) {
                        $buttons[] = [
                            'label' => 'Download',
                            'url' => $baseUrl . '/files/digital/download/' . $fileId,
                            'class' => 'btn-outline-success'
                        ];
                    }
                }
                
                // View button - available to all authenticated users
                if ($fileId) {
                    $buttons[] = [
                        'label' => 'View Details',
                        'url' => $baseUrl . '/files/digital/view/' . $fileId,
                        'class' => 'btn-outline-primary'
                    ];
                }
            }
        @endphp
        
        @if(count($buttons) > 0)
        <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <div style="font-size: 11px; font-weight: bold; color: #495057; margin-bottom: 8px; text-align: center;">
                Quick Actions
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; align-items: center;">
                @foreach($buttons as $button)
                <a href="{{ $button['url'] }}" 
                   target="_blank"
                   style="display: inline-block; padding: 6px 12px; font-size: 10px; text-decoration: none; border: 1px solid #940000; border-radius: 3px; background-color: white; color: #940000; font-weight: 500; transition: all 0.2s;">
                    {{ $button['label'] }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    @endif
</div>

