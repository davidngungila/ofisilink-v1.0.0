<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use App\Models\SystemSetting;
use App\Models\NotificationProvider;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Mail\Message;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:System Admin');
    }

    public function index()
    {
        // Get organization settings from OrganizationSetting table (financial year, etc.)
        $orgSettings = OrganizationSetting::getSettings();
        
        // Initialize financial year dates if not set
        if (!$orgSettings->financial_year_start_date) {
            $orgSettings->initializeFinancialYear();
        }
        
        // Get financial year history
        $financialYearHistory = $orgSettings->financial_year_history ?? [];
        
        // Get organization info from SystemSetting table (database-driven)
        $organizationSettings = SystemSetting::whereIn('key', [
            'company_name', 'trading_name', 'address', 'phone', 'email', 'website', 'tax_id'
        ])->get()->keyBy('key');
        
        // Get all other system settings (key-value pairs)
        $systemSettings = SystemSetting::orderBy('key')->get();
        
        // Get current admin user for profile
        $adminUser = Auth::user();
        
        return view('admin.settings', compact(
            'orgSettings', 
            'financialYearHistory', 
            'organizationSettings', 
            'systemSettings', 
            'adminUser'
        ));
    }

    public function organizationSettings()
    {
        // Get organization settings from OrganizationSetting table
        $orgSettings = OrganizationSetting::getSettings();
        
        // Initialize financial year dates if not set
        if (!$orgSettings->financial_year_start_date) {
            $orgSettings->initializeFinancialYear();
        }
        
        // Get financial year history
        $financialYearHistory = $orgSettings->financial_year_history ?? [];
        
        // Get organization info from SystemSetting table
        $organizationSettings = SystemSetting::whereIn('key', [
            'company_name', 'trading_name', 'address', 'phone', 'email', 'website', 'tax_id'
        ])->get()->keyBy('key');
        
        return view('admin.settings.organization.index', compact(
            'orgSettings', 
            'financialYearHistory',
            'organizationSettings'
        ));
    }
    
    /**
     * Individual System Settings page
     */
    public function systemSettings()
    {
        // Get all system settings (key-value pairs)
        $systemSettings = SystemSetting::orderBy('key')->get();
        
        return view('admin.settings.system.index', compact('systemSettings'));
    }
    
    /**
     * Individual Communication Settings page
     */
    public function communicationSettings()
    {
        // Get communication settings
        $settings = [
            'sms' => [
                'username' => SystemSetting::getValue('sms_username', ''),
                'password' => SystemSetting::getValue('sms_password', ''),
                'from' => SystemSetting::getValue('sms_from', ''),
                'url' => SystemSetting::getValue('sms_url', ''),
            ],
            'email' => [
                'mailer' => SystemSetting::getValue('mail_mailer', config('mail.default')),
                'host' => SystemSetting::getValue('mail_host', config('mail.mailers.smtp.host')),
                'port' => SystemSetting::getValue('mail_port', config('mail.mailers.smtp.port')),
                'username' => SystemSetting::getValue('mail_username', config('mail.mailers.smtp.username')),
                'password' => SystemSetting::getValue('mail_password', ''),
                'encryption' => SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption')),
                'from_address' => SystemSetting::getValue('mail_from_address', config('mail.from.address')),
                'from_name' => SystemSetting::getValue('mail_from_name', config('mail.from.name')),
            ],
        ];
        
        // Get all notification providers for display
        $emailProviders = NotificationProvider::where('type', 'email')
            ->orderBy('is_primary', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $smsProviders = NotificationProvider::where('type', 'sms')
            ->orderBy('is_primary', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('admin.settings.communication.index', compact('settings', 'emailProviders', 'smsProviders'));
    }
    
    /**
     * Individual Profile & Image page
     */
    public function profileSettings()
    {
        // Get current admin user for profile
        $adminUser = Auth::user();
        
        return view('admin.settings.profile.index', compact('adminUser'));
    }
    
    public function organizationInfo()
    {
        $orgSettings = OrganizationSetting::getSettings();
        $organizationSettings = SystemSetting::whereIn('key', [
            'company_name', 'trading_name', 'address', 'phone', 'email', 'website', 'tax_id'
        ])->get()->keyBy('key');
        
        return view('admin.settings.organization.info', compact(
            'orgSettings',
            'organizationSettings'
        ));
    }
    
    public function financialYear()
    {
        $orgSettings = OrganizationSetting::getSettings();
        
        if (!$orgSettings->financial_year_start_date) {
            $orgSettings->initializeFinancialYear();
        }
        
        $financialYearHistory = $orgSettings->financial_year_history ?? [];
        
        return view('admin.settings.organization.financial-year', compact(
            'orgSettings',
            'financialYearHistory'
        ));
    }
    
    public function currency()
    {
        $orgSettings = OrganizationSetting::getSettings();
        
        return view('admin.settings.organization.currency', compact('orgSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_registration_number' => 'nullable|string|max:100',
            'company_tax_id' => 'nullable|string|max:100',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:100',
            'company_state' => 'nullable|string|max:100',
            'company_country' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:20',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'required|email|max:255',
            'company_website' => 'nullable|url|max:255',
            
            // Currency Settings
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'currency_position' => 'required|in:prefix,suffix',
            'decimal_places' => 'required|integer|min:0|max:4',
            'number_format' => 'required|string',
            
            // Financial Year Settings
            'financial_year_start_month' => 'required|string|size:2',
            'financial_year_start_day' => 'required|string|size:2',
            'current_financial_year' => 'required|integer|min:2000|max:2100',
            
            // Date & Time
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'week_start_day' => 'required|in:monday,sunday',
            'first_day_of_month' => 'nullable|integer|in:0,1',
            'locale' => 'nullable|string|max:10',
            'country_code' => 'nullable|string|max:2',
            
            // System Settings
            'max_file_size' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|string',
            
            // Business Hours
            'business_hours_start' => 'required|date_format:H:i',
            'business_hours_end' => 'required|date_format:H:i',
            'business_days' => 'nullable|array',
            
            // Payroll
            'payroll_period_days' => 'required|integer|min:1|max:365',
            'payroll_processing_day' => 'required|integer|min:1|max:31',
            'payroll_currency' => 'nullable|string|max:3',
            
            // Leave
            'default_annual_leave_days' => 'required|integer|min:0|max:365',
            'default_sick_leave_days' => 'required|integer|min:0|max:365',
            'max_consecutive_leave_days' => 'nullable|integer|min:1|max:365',
            
            // Tax
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'income_tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive_pricing' => 'nullable|in:0,1,true,false',
            
            // Notifications
            'email_notifications_enabled' => 'nullable|in:0,1,true,false',
            'sms_notifications_enabled' => 'nullable|in:0,1,true,false',
            'push_notifications_enabled' => 'nullable|in:0,1,true,false',
            
            // Integration
            'integration_settings' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        // Validate logo and favicon separately if provided
        if ($request->hasFile('company_logo')) {
            $request->validate([
                'company_logo' => 'file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            ]);
        }
        
        if ($request->hasFile('company_favicon')) {
            $request->validate([
                'company_favicon' => 'file|image|mimes:ico,png,jpg|max:512',
            ]);
        }

        try {
            DB::beginTransaction();
            
            $settings = OrganizationSetting::getSettings();
            
            // Check if financial year is locked
            if ($settings->financial_year_locked && $request->has('current_financial_year')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Financial year is locked. Cannot change during active period.'
                ], 403);
            }
            
            // Handle financial year change or initialization
            if ($request->has('initialize_dates') && $request->initialize_dates) {
                if ($request->current_financial_year != $settings->current_financial_year) {
                    $history = $settings->financial_year_history ?? [];
                    $history[] = [
                        'old_year' => $settings->current_financial_year,
                        'new_year' => $request->current_financial_year,
                        'changed_at' => now()->toDateTimeString(),
                        'changed_by' => Auth::id(),
                    ];
                    $request->merge(['financial_year_history' => $history]);
                }
                
                // Initialize financial year dates
                $dates = $settings->getFinancialYearDates($request->current_financial_year);
                $request->merge([
                    'financial_year_start_date' => $dates['start'],
                    'financial_year_end_date' => $dates['end'],
                ]);
            } elseif (!$settings->financial_year_start_date) {
                // Initialize if not set
                $dates = $settings->getFinancialYearDates();
                $request->merge([
                    'financial_year_start_date' => $dates['start'],
                    'financial_year_end_date' => $dates['end'],
                ]);
            }
            
            // Update organization info in SystemSetting table if provided
            if ($request->has('organization_settings')) {
                foreach ($request->organization_settings as $key => $value) {
                    SystemSetting::setValue($key, $value, 'text');
                }
            }
            
            // Handle integration_settings JSON
            if ($request->has('integration_settings') && $request->integration_settings) {
                try {
                    $integrationSettings = json_decode($request->integration_settings, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $request->merge(['integration_settings' => $integrationSettings]);
                    } else {
                        throw new \Exception('Invalid JSON format in integration settings');
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON format in integration settings: ' . $e->getMessage()
                    ], 422);
                }
            }
            
            // Handle business hours time format - convert to datetime format
            if ($request->has('business_hours_start')) {
                $time = $request->business_hours_start;
                if (strlen($time) == 5) { // H:i format
                    $request->merge(['business_hours_start' => now()->format('Y-m-d') . ' ' . $time . ':00']);
                }
            }
            if ($request->has('business_hours_end')) {
                $time = $request->business_hours_end;
                if (strlen($time) == 5) { // H:i format
                    $request->merge(['business_hours_end' => now()->format('Y-m-d') . ' ' . $time . ':00']);
                }
            }
            
            // Update settings - exclude boolean fields from only() as they need special handling
            $updateData = $request->only([
                'company_name', 'company_registration_number', 'company_tax_id',
                'company_address', 'company_city', 'company_state', 'company_country', 'company_postal_code',
                'company_phone', 'company_email', 'company_website',
                'currency', 'currency_symbol', 'currency_position', 'decimal_places', 'number_format',
                'financial_year_start_month', 'financial_year_start_day',
                'current_financial_year', 'financial_year_start_date', 'financial_year_end_date',
                'financial_year_history',
                'timezone', 'date_format', 'time_format', 'week_start_day', 'first_day_of_month',
                'locale', 'country_code',
                'max_file_size', 'allowed_file_types',
                'business_hours_start', 'business_hours_end', 'business_days',
                'payroll_period_days', 'payroll_processing_day', 'payroll_currency',
                'default_annual_leave_days', 'default_sick_leave_days', 'max_consecutive_leave_days',
                'vat_rate', 'income_tax_rate',
                'integration_settings', 'internal_notes',
            ]);
            
            // Handle boolean fields - checkboxes now send '1' when checked, '0' when unchecked (via hidden inputs)
            $updateData['tax_inclusive_pricing'] = filter_var($request->input('tax_inclusive_pricing', 0), FILTER_VALIDATE_BOOLEAN);
            $updateData['email_notifications_enabled'] = filter_var($request->input('email_notifications_enabled', 0), FILTER_VALIDATE_BOOLEAN);
            $updateData['sms_notifications_enabled'] = filter_var($request->input('sms_notifications_enabled', 0), FILTER_VALIDATE_BOOLEAN);
            $updateData['push_notifications_enabled'] = filter_var($request->input('push_notifications_enabled', 0), FILTER_VALIDATE_BOOLEAN);
            
            // Handle logo upload - check for file upload first
            if ($request->hasFile('company_logo')) {
                try {
                    $logo = $request->file('company_logo');
                    
                    // Validate file exists and is valid
                    if (!$logo || !$logo->isValid()) {
                        throw new \Exception('Invalid or corrupted file uploaded');
                    }
                    
                    // Get file extension
                    $extension = strtolower($logo->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        throw new \Exception('Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
                    }
                    
                    // Check file size (5MB = 5120 KB)
                    if ($logo->getSize() > 5120 * 1024) {
                        throw new \Exception('File size exceeds 5MB limit');
                    }
                    
                    $logoName = 'company-logo-' . time() . '-' . uniqid() . '.' . $extension;
                    
                    // Ensure settings directory exists
                    $settingsPath = storage_path('app/public/settings');
                    if (!is_dir($settingsPath)) {
                        if (!mkdir($settingsPath, 0755, true) && !is_dir($settingsPath)) {
                            throw new \Exception('Failed to create settings directory. Check permissions.');
                        }
                    }
                    
                    // Store the file
                    $stored = $logo->storeAs('public/settings', $logoName);
                    
                    if (!$stored) {
                        throw new \Exception('Failed to store logo file. Check storage permissions.');
                    }
                    
                    // Delete old logo if exists
                    if ($settings->company_logo) {
                        $oldLogoPath = storage_path('app/public/' . $settings->company_logo);
                        if (file_exists($oldLogoPath)) {
                            @unlink($oldLogoPath);
                        }
                    }
                    
                    $updateData['company_logo'] = 'settings/' . $logoName;
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Logo upload error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Company logo upload failed: ' . $e->getMessage(),
                        'error' => $e->getMessage()
                    ], 422);
                }
            }
            
            // Handle favicon upload - check for file upload first
            if ($request->hasFile('company_favicon')) {
                try {
                    $favicon = $request->file('company_favicon');
                    
                    // Validate file exists and is valid
                    if (!$favicon || !$favicon->isValid()) {
                        throw new \Exception('Invalid or corrupted file uploaded');
                    }
                    
                    // Get file extension
                    $extension = strtolower($favicon->getClientOriginalExtension());
                    $allowedExtensions = ['ico', 'png', 'jpg'];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        throw new \Exception('Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
                    }
                    
                    // Check file size (512 KB)
                    if ($favicon->getSize() > 512 * 1024) {
                        throw new \Exception('File size exceeds 512KB limit');
                    }
                    
                    $faviconName = 'company-favicon-' . time() . '-' . uniqid() . '.' . $extension;
                    
                    // Ensure settings directory exists
                    $settingsPath = storage_path('app/public/settings');
                    if (!file_exists($settingsPath)) {
                        if (!is_dir($settingsPath)) {
                            mkdir($settingsPath, 0755, true);
                        }
                    }
                    
                    // Store the file
                    $stored = $favicon->storeAs('public/settings', $faviconName);
                    
                    if (!$stored) {
                        throw new \Exception('Failed to store favicon file. Check storage permissions.');
                    }
                    
                    // Delete old favicon if exists
                    if ($settings->company_favicon) {
                        $oldFaviconPath = storage_path('app/public/' . $settings->company_favicon);
                        if (file_exists($oldFaviconPath)) {
                            @unlink($oldFaviconPath);
                        }
                    }
                    
                    $updateData['company_favicon'] = 'settings/' . $faviconName;
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Favicon upload error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Company favicon upload failed: ' . $e->getMessage(),
                        'error' => $e->getMessage()
                    ], 422);
                }
            }
            
            $updateData['updated_by'] = Auth::id();
            $updateData['updated_at'] = now();
            
            $settings->update($updateData);
            
            // Save critical settings to SystemSetting for system-wide use
            // File Upload Settings
            SystemSetting::setValue('max_file_size', $updateData['max_file_size'] ?? 10, 'number', 'Maximum file upload size in MB');
            SystemSetting::setValue('allowed_file_types', $updateData['allowed_file_types'] ?? 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'text', 'Comma-separated list of allowed file types');
            
            // Date & Time Settings
            SystemSetting::setValue('timezone', $updateData['timezone'] ?? config('app.timezone'), 'text', 'System timezone');
            SystemSetting::setValue('date_format', $updateData['date_format'] ?? 'Y-m-d', 'text', 'Date format for display');
            SystemSetting::setValue('time_format', $updateData['time_format'] ?? 'H:i:s', 'text', 'Time format for display');
            SystemSetting::setValue('week_start_day', $updateData['week_start_day'] ?? 'monday', 'text', 'Week start day');
            SystemSetting::setValue('locale', $updateData['locale'] ?? 'en', 'text', 'System locale');
            
            // Currency & Regional Settings
            SystemSetting::setValue('currency', $updateData['currency'] ?? 'TZS', 'text', 'System currency code');
            SystemSetting::setValue('currency_symbol', $updateData['currency_symbol'] ?? 'TSh', 'text', 'Currency symbol');
            SystemSetting::setValue('currency_position', $updateData['currency_position'] ?? 'prefix', 'text', 'Currency symbol position');
            SystemSetting::setValue('decimal_places', $updateData['decimal_places'] ?? 2, 'number', 'Number of decimal places for currency');
            SystemSetting::setValue('number_format', $updateData['number_format'] ?? '1,234.56', 'text', 'Number format pattern');
            SystemSetting::setValue('country_code', $updateData['country_code'] ?? 'TZ', 'text', 'Country code');
            
            // Notification Settings
            SystemSetting::setValue('email_notifications_enabled', $updateData['email_notifications_enabled'] ? '1' : '0', 'boolean', 'Enable Email Notifications');
            SystemSetting::setValue('sms_notifications_enabled', $updateData['sms_notifications_enabled'] ? '1' : '0', 'boolean', 'Enable SMS Notifications');
            SystemSetting::setValue('push_notifications_enabled', $updateData['push_notifications_enabled'] ? '1' : '0', 'boolean', 'Enable Push Notifications');
            
            // Update application timezone if changed
            if (isset($updateData['timezone'])) {
                config(['app.timezone' => $updateData['timezone']]);
            }
            
            DB::commit();
            
            $updatedSettings = $settings->fresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Organization settings updated successfully',
                'settings' => [
                    'company_logo' => $updatedSettings->company_logo,
                    'company_favicon' => $updatedSettings->company_favicon,
                    'company_name' => $updatedSettings->company_name,
                ],
                'full_settings' => $updatedSettings
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleFinancialYearLock(Request $request)
    {
        $settings = OrganizationSetting::getSettings();
        
        $settings->update([
            'financial_year_locked' => !$settings->financial_year_locked,
            'updated_by' => Auth::id(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $settings->financial_year_locked 
                ? 'Financial year locked' 
                : 'Financial year unlocked',
            'locked' => $settings->financial_year_locked
        ]);
    }

    public function initializeFinancialYear(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);
        
        $settings = OrganizationSetting::getSettings();
        
        // Check if locked
        if ($settings->financial_year_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot initialize new financial year while current year is locked'
            ], 403);
        }
        
        $dates = $settings->getFinancialYearDates($request->year);
        
        $settings->update([
            'current_financial_year' => $request->year,
            'financial_year_start_date' => $dates['start'],
            'financial_year_end_date' => $dates['end'],
            'updated_by' => Auth::id(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Financial year initialized successfully',
            'dates' => [
                'start' => $dates['start']->format('Y-m-d'),
                'end' => $dates['end']->format('Y-m-d'),
                'year' => $dates['year'],
            ]
        ]);
    }

    public function getSettings()
    {
        $settings = OrganizationSetting::getSettings();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $logo = $request->file('logo');
        $logoName = 'company-logo-' . time() . '.' . $logo->getClientOriginalExtension();
        $logo->storeAs('public/settings', $logoName);

        $settings = OrganizationSetting::getSettings();
        
        // Delete old logo
        if ($settings->company_logo && Storage::exists('public/' . $settings->company_logo)) {
            Storage::delete('public/' . $settings->company_logo);
        }
        
        $settings->update([
            'company_logo' => 'settings/' . $logoName,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'logo_url' => asset('storage/settings/' . $logoName)
        ]);
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,jpg|max:512'
        ]);

        $favicon = $request->file('favicon');
        $faviconName = 'company-favicon-' . time() . '.' . $favicon->getClientOriginalExtension();
        $favicon->storeAs('public/settings', $faviconName);

        $settings = OrganizationSetting::getSettings();
        
        // Delete old favicon
        if ($settings->company_favicon && Storage::exists('public/' . $settings->company_favicon)) {
            Storage::delete('public/' . $settings->company_favicon);
        }
        
        $settings->update([
            'company_favicon' => 'settings/' . $faviconName,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favicon uploaded successfully',
            'favicon_url' => asset('storage/settings/' . $faviconName)
        ]);
    }

    // System Settings CRUD
    public function getSystemSettings()
    {
        $settings = SystemSetting::orderBy('key')->get();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    public function storeSystemSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:system_settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:text,number,boolean,date,email,url,textarea',
            'description' => 'nullable|string|max:1000'
        ]);

        $setting = SystemSetting::create([
            'key' => $request->key,
            'value' => $request->value ?? '',
            'type' => $request->type,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully',
            'setting' => $setting
        ]);
    }

    public function updateSystemSetting(Request $request, $id)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:system_settings,key,' . $id,
            'value' => 'nullable|string',
            'type' => 'required|in:text,number,boolean,date,email,url,textarea',
            'description' => 'nullable|string|max:1000'
        ]);

        $setting = SystemSetting::findOrFail($id);
        $setting->update([
            'key' => $request->key,
            'value' => $request->value ?? '',
            'type' => $request->type,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'setting' => $setting
        ]);
    }

    public function deleteSystemSetting($id)
    {
        $setting = SystemSetting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }

    public function updateAdminProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function updateAdminPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB
        ]);

        $user = Auth::user();
        
        // Delete old photo
        if ($user->photo && Storage::exists('public/photos/' . $user->photo)) {
            Storage::delete('public/photos/' . $user->photo);
        }

        // Store new photo
        $photo = $request->file('photo');
        $photoName = 'admin-' . $user->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('public/photos', $photoName);

        $user->update(['photo' => $photoName]);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully',
            'photo_url' => asset('storage/photos/' . $photoName)
        ]);
    }

    /**
     * Get communication settings (SMS and Email)
     */
    public function getCommunicationSettings()
    {
        $settings = [
            'sms' => [
                'username' => SystemSetting::getValue('sms_username', ''),
                'password' => SystemSetting::getValue('sms_password', ''),
                'from' => SystemSetting::getValue('sms_from', ''),
                'url' => SystemSetting::getValue('sms_url', ''),
            ],
            'email' => [
                'mailer' => SystemSetting::getValue('mail_mailer', config('mail.default')),
                'host' => SystemSetting::getValue('mail_host', config('mail.mailers.smtp.host')),
                'port' => SystemSetting::getValue('mail_port', config('mail.mailers.smtp.port')),
                'username' => SystemSetting::getValue('mail_username', config('mail.mailers.smtp.username')),
                'password' => SystemSetting::getValue('mail_password', ''),
                'encryption' => SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption')),
                'from_address' => SystemSetting::getValue('mail_from_address', config('mail.from.address')),
                'from_name' => SystemSetting::getValue('mail_from_name', config('mail.from.name')),
            ],
        ];

        // Check if this is an AJAX/API request
        if (request()->ajax() || 
            request()->wantsJson() || 
            request()->expectsJson() || 
            request()->routeIs('admin.settings.communication.data') ||
            request()->header('Accept') === 'application/json' ||
            request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        }

        // Get all notification providers for display
        $emailProviders = NotificationProvider::where('type', 'email')
            ->orderBy('is_primary', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $smsProviders = NotificationProvider::where('type', 'sms')
            ->orderBy('is_primary', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Return view for regular GET requests
        return view('admin.settings.communication', compact('settings', 'emailProviders', 'smsProviders'));
    }

    /**
     * Update communication settings
     */
    public function updateCommunicationSettings(Request $request)
    {
        $request->validate([
            'sms_username' => 'nullable|string|max:255',
            'sms_password' => 'nullable|string|max:255',
            'sms_from' => 'nullable|string|max:100',
            'sms_url' => 'nullable|url|max:500',
            'mail_mailer' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        try {
            // Update SMS settings
            if ($request->has('sms_username')) {
                SystemSetting::setValue('sms_username', $request->sms_username, 'text', 'SMS Gateway Username');
            }
            if ($request->has('sms_password')) {
                SystemSetting::setValue('sms_password', $request->sms_password, 'text', 'SMS Gateway Password');
            }
            if ($request->has('sms_from')) {
                SystemSetting::setValue('sms_from', $request->sms_from, 'text', 'SMS Sender Name');
            }
            if ($request->has('sms_url')) {
                SystemSetting::setValue('sms_url', $request->sms_url, 'url', 'SMS Gateway API URL');
            }

            // Update Email settings
            if ($request->has('mail_mailer')) {
                SystemSetting::setValue('mail_mailer', $request->mail_mailer, 'text', 'Email Mailer Type');
            }
            if ($request->has('mail_host')) {
                SystemSetting::setValue('mail_host', $request->mail_host, 'text', 'SMTP Host');
            }
            if ($request->has('mail_port')) {
                SystemSetting::setValue('mail_port', $request->mail_port, 'number', 'SMTP Port');
            }
            if ($request->has('mail_username')) {
                SystemSetting::setValue('mail_username', $request->mail_username, 'email', 'SMTP Username');
            }
            if ($request->has('mail_password')) {
                SystemSetting::setValue('mail_password', $request->mail_password, 'text', 'SMTP Password');
            }
            if ($request->has('mail_encryption')) {
                SystemSetting::setValue('mail_encryption', $request->mail_encryption, 'text', 'SMTP Encryption');
            }
            if ($request->has('mail_from_address')) {
                SystemSetting::setValue('mail_from_address', $request->mail_from_address, 'email', 'Email From Address');
            }
            if ($request->has('mail_from_name')) {
                SystemSetting::setValue('mail_from_name', $request->mail_from_name, 'text', 'Email From Name');
            }
            
            // Save notification settings to database
            if ($request->has('email_notifications_enabled')) {
                SystemSetting::setValue('email_notifications_enabled', $request->email_notifications_enabled ? '1' : '0', 'boolean', 'Enable Email Notifications');
            }
            if ($request->has('sms_notifications_enabled')) {
                SystemSetting::setValue('sms_notifications_enabled', $request->sms_notifications_enabled ? '1' : '0', 'boolean', 'Enable SMS Notifications');
            }
            if ($request->has('push_notifications_enabled')) {
                SystemSetting::setValue('push_notifications_enabled', $request->push_notifications_enabled ? '1' : '0', 'boolean', 'Enable Push Notifications');
            }

            return response()->json([
                'success' => true,
                'message' => 'Communication settings updated successfully',
                'settings' => $this->getCommunicationSettings()->getData(true)['settings']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating communication settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test SMS configuration
     */
    public function testSMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (!str_starts_with($phone, '255')) {
                $phone = '255' . ltrim($phone, '0');
            }
            
            $notifier = app(\App\Services\NotificationService::class);
            $result = $notifier->sendSMS($phone, 'Test SMS from OfisiLink System. If you receive this, SMS configuration is working correctly.');

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully to ' . $phone
                ]);
            } else {
                // Get last error from logs or provide detailed message
                \Log::error('SMS test failed', [
                    'phone' => $phone,
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'SMS sending failed. Please check: 1) SMS credentials are correct, 2) Phone number format is valid (255XXXXXXXXX), 3) SMS gateway URL is accessible. Check logs for more details.'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('SMS test exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $request->phone
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'SMS test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $emailProvider = null;
        
        try {
            // Get primary email provider from NotificationProvider table
            $emailProvider = NotificationProvider::getPrimary('email');
            
            if (!$emailProvider) {
                // Fallback to SystemSetting if no provider found
                \Log::warning('No primary email provider found, using SystemSetting fallback');
                $this->updateMailConfigFromSettings();
            } else {
                // Use primary email provider settings
                $this->configureMailFromProvider($emailProvider);
                
                \Log::info('Using primary email provider for test', [
                    'provider_id' => $emailProvider->id,
                    'provider_name' => $emailProvider->name,
                    'host' => $emailProvider->mail_host,
                    'port' => $emailProvider->mail_port,
                    'username' => $emailProvider->mail_username ? '***' : 'empty'
                ]);
            }
            
            // Use provider's testEmail method which includes auto-correction for Gmail
            if ($emailProvider) {
                $testResult = $emailProvider->testEmail($request->email);
                
                if ($testResult['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Test email sent successfully using ' . $this->sanitizeForJson($emailProvider->name ?? 'provider'),
                        'connection_status' => 'connected'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $this->sanitizeForJson($testResult['message'] ?? 'Failed to send test email'),
                        'error' => $this->sanitizeForJson($testResult['error'] ?? 'Unknown error'),
                        'suggestion' => $this->sanitizeForJson($testResult['suggestion'] ?? null),
                        'connection_status' => 'disconnected'
                    ], 400);
                }
            }
            
            // Fallback: Use EmailService directly
            $emailService = new EmailService();
            $emailService->updateConfig([
                'host' => SystemSetting::getValue('mail_host') ?: env('MAIL_HOST', 'smtp.gmail.com'),
                'port' => SystemSetting::getValue('mail_port') ?: env('MAIL_PORT', 587),
                'username' => SystemSetting::getValue('mail_username') ?: env('MAIL_USERNAME', ''),
                'password' => SystemSetting::getValue('mail_password') ?: env('MAIL_PASSWORD', ''),
                'encryption' => SystemSetting::getValue('mail_encryption') ?: env('MAIL_ENCRYPTION', 'tls'),
                'from_address' => SystemSetting::getValue('mail_from_address') ?: env('MAIL_FROM_ADDRESS', ''),
                'from_name' => SystemSetting::getValue('mail_from_name') ?: env('MAIL_FROM_NAME', 'OfisiLink'),
            ]);
            
            $testResult = $emailService->testConfiguration($request->email);
            
            if ($testResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully using system settings',
                    'connection_status' => 'connected'
                ]);
            } else {
                $errorMessage = $this->sanitizeForJson($testResult['message'] ?? 'Failed to send test email');
                $errorDetails = $this->sanitizeForJson($testResult['error'] ?? 'Unknown error');
                $suggestion = $this->sanitizeForJson($testResult['suggestion'] ?? null);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $errorDetails,
                    'suggestion' => $suggestion,
                    'connection_status' => 'disconnected'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Email test error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'provider_id' => $emailProvider->id ?? null,
                'provider_name' => $emailProvider->name ?? null,
                'host' => $emailProvider->mail_host ?? null,
                'port' => $emailProvider->mail_port ?? null
            ]);
            
            $suggestion = 'Check firewall settings, verify SMTP host and port are correct, and ensure internet connection is active.';
            
            // Sanitize error message to ensure valid UTF-8
            $errorMessage = $this->sanitizeForJson($e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Email test failed: ' . $errorMessage,
                'error' => $errorMessage,
                'suggestion' => $suggestion
            ], 500);
        }
    }

    /**
     * Sanitize string for JSON encoding (ensure valid UTF-8)
     */
    protected function sanitizeForJson($value)
    {
        if ($value === null) {
            return null;
        }
        
        if (!is_string($value)) {
            $value = (string)$value;
        }
        
        // Remove invalid UTF-8 characters
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        
        // Remove any remaining invalid UTF-8 sequences
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Ensure it's valid UTF-8
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, 'UTF-8, ISO-8859-1', true));
        }
        
        return $value;
    }

    /**
     * Configure mail settings from NotificationProvider
     */
    protected function configureMailFromProvider(NotificationProvider $provider)
    {
        // Create stream context with SSL verification disabled
        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
        
        config([
            'mail.default' => $provider->mailer_type ?? 'smtp',
            'mail.mailers.smtp.host' => $provider->mail_host ?? '',
            'mail.mailers.smtp.port' => $provider->mail_port ?? 587,
            'mail.mailers.smtp.username' => $provider->mail_username ?? '',
            'mail.mailers.smtp.password' => $provider->mail_password ?? '',
            'mail.mailers.smtp.encryption' => $provider->mail_encryption ?? 'tls',
            'mail.from.address' => $provider->mail_from_address ?? '',
            'mail.from.name' => $provider->mail_from_name ?? 'OfisiLink',
            // Use stream context from config/mail.php or create new one
            'mail.mailers.smtp.stream' => $streamContext,
            // Ensure stream SSL settings match config/mail.php
            'mail.mailers.smtp.stream.ssl' => [
                'allow_self_signed' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        // Also set default stream context as fallback
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
    }

    /**
     * Set stream context to disable SSL verification (for self-signed certificates)
     */
    protected function setMailStreamContext()
    {
        // Set the default stream context for all streams
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
    }

    /**
     * Configure mailer transport to disable SSL verification
     */
    protected function configureMailerTransport()
    {
        // Set OpenSSL configuration to disable verification
        // This is a workaround for Symfony Mailer which may not use stream_context_set_default
        if (function_exists('ini_set')) {
            // Disable OpenSSL verification at PHP level
            @ini_set('openssl.cafile', '');
            @ini_set('openssl.capath', '');
        }
        
        // Also try to set environment variable (some mailers check this)
        putenv('SMTP_SSL_VERIFY_PEER=false');
        $_ENV['SMTP_SSL_VERIFY_PEER'] = 'false';
    }

    /**
     * Configure mailer transport with stream context to disable SSL verification
     */
    protected function configureMailerTransportWithStreamContext($streamContext = null)
    {
        // If stream context is provided, ensure it's set in config
        if ($streamContext) {
            config(['mail.mailers.smtp.stream' => $streamContext]);
        }
        
        // Set OpenSSL configuration to disable verification
        if (function_exists('ini_set')) {
            @ini_set('openssl.cafile', '');
            @ini_set('openssl.capath', '');
        }
        
        // Set environment variables
        putenv('SMTP_SSL_VERIFY_PEER=false');
        $_ENV['SMTP_SSL_VERIFY_PEER'] = 'false';
        
        // Ensure default stream context is set
        if (!$streamContext) {
            stream_context_set_default([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);
        }
    }

    /**
     * Send email using EmailService (PHPMailer) with provider settings
     */
    protected function sendEmailWithCustomTransport($email, $provider = null)
    {
        $emailService = new EmailService();
        
        // Configure EmailService with provider settings
        if ($provider) {
            $emailService->updateConfig([
                'host' => $provider->mail_host ?? 'smtp.gmail.com',
                'port' => $provider->mail_port ?? 587,
                'username' => $provider->mail_username ?? '',
                'password' => $provider->mail_password ?? '',
                'encryption' => $provider->mail_encryption ?? 'tls',
                'from_address' => $provider->mail_from_address ?? '',
                'from_name' => $provider->mail_from_name ?? 'OfisiLink',
            ]);
        }
        
        // Render email template
        $emailBody = View::make('emails.notification', [
            'emailMessage' => 'This is a test email from OfisiLink System. If you receive this, email configuration is working correctly.',
            'data' => []
        ])->render();
        
        // Send email using EmailService
        return $emailService->send($email, 'OfisiLink Email Test', $emailBody);
    }

    /**
     * Update mail configuration from database settings
     */
    protected function updateMailConfigFromSettings()
    {
        $mailer = SystemSetting::getValue('mail_mailer', config('mail.default', 'smtp'));
        $host = SystemSetting::getValue('mail_host', config('mail.mailers.smtp.host', ''));
        $port = SystemSetting::getValue('mail_port', config('mail.mailers.smtp.port', 587));
        $username = SystemSetting::getValue('mail_username', config('mail.mailers.smtp.username', ''));
        $password = SystemSetting::getValue('mail_password', config('mail.mailers.smtp.password', ''));
        $encryption = SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $fromAddress = SystemSetting::getValue('mail_from_address', config('mail.from.address', ''));
        $fromName = SystemSetting::getValue('mail_from_name', config('mail.from.name', 'OfisiLink'));

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);
    }

    /**
     * Check Email connection status
     */
    public function checkEmailStatus()
    {
        try {
            // Get primary email provider from NotificationProvider table
            $emailProvider = NotificationProvider::getPrimary('email');
            
            if ($emailProvider) {
                // Use primary email provider settings
                $host = $emailProvider->mail_host ?? '';
                $port = $emailProvider->mail_port ?? 587;
                $username = $emailProvider->mail_username ?? '';
                $password = $emailProvider->mail_password ?? '';
            } else {
                // Fallback to SystemSetting
                $this->updateMailConfigFromSettings();
                $host = SystemSetting::getValue('mail_host', '');
                $port = SystemSetting::getValue('mail_port', 587);
                $username = SystemSetting::getValue('mail_username', '');
                $password = SystemSetting::getValue('mail_password', '');
            }
            
            if (empty($host) || empty($port)) {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'message' => 'Email configuration incomplete (missing host or port)'
                ]);
            }
            
            // Try to connect to SMTP server
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if ($connection) {
                fclose($connection);
                return response()->json([
                    'success' => true,
                    'status' => 'connected',
                    'message' => 'Email server connection successful',
                    'host' => $host,
                    'port' => $port
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'message' => 'Cannot connect to email server: ' . ($errstr ?? 'Connection timeout'),
                    'host' => $host,
                    'port' => $port
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Error checking email status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check SMS connection status
     */
    public function checkSMSStatus()
    {
        try {
            // Get primary SMS provider from NotificationProvider table
            $smsProvider = NotificationProvider::getPrimary('sms');
            
            if ($smsProvider) {
                // Use primary SMS provider settings
                $username = $smsProvider->sms_username ?? '';
                $password = $smsProvider->sms_password ?? '';
                $url = $smsProvider->sms_url ?? '';
            } else {
                // Fallback to SystemSetting
                $username = SystemSetting::getValue('sms_username', '');
                $password = SystemSetting::getValue('sms_password', '');
                $url = SystemSetting::getValue('sms_url', '');
            }
            
            if (empty($username) || empty($password) || empty($url)) {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'message' => 'SMS configuration incomplete'
                ]);
            }
            
            // Try to parse URL to check if it's valid
            $parsedUrl = parse_url($url);
            if (!$parsedUrl || !isset($parsedUrl['host'])) {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'message' => 'Invalid SMS gateway URL'
                ]);
            }
            
            // Try to connect to SMS gateway host
            $host = $parsedUrl['host'];
            $port = $parsedUrl['port'] ?? (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https' ? 443 : 80);
            
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if ($connection) {
                fclose($connection);
                return response()->json([
                    'success' => true,
                    'status' => 'connected',
                    'message' => 'SMS gateway connection successful',
                    'url' => $url
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'message' => 'Cannot connect to SMS gateway: ' . ($errstr ?? 'Connection timeout'),
                    'url' => $url
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Error checking SMS status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all notification providers
     */
    public function getNotificationProviders(Request $request)
    {
        try {
            $type = $request->get('type'); // 'email' or 'sms'
            
            $query = NotificationProvider::query();
            
            if ($type) {
                $query->where('type', $type);
            }
            
            $providers = $query->orderBy('type')
                ->orderBy('is_primary', 'desc')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Check connection status for each provider
            $providers = $providers->map(function($provider) {
                try {
                    $status = $provider->checkConnectionStatus();
                } catch (\Exception $e) {
                    \Log::warning('Error checking connection status for provider ' . $provider->id, [
                        'error' => $e->getMessage()
                    ]);
                    $status = [
                        'status' => 'error',
                        'message' => 'Error checking connection: ' . $e->getMessage()
                    ];
                }
                
                return [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'type' => $provider->type,
                    'is_active' => $provider->is_active,
                    'is_primary' => $provider->is_primary,
                    'priority' => $provider->priority,
                    'description' => $provider->description,
                    'status' => $status['status'] ?? 'unknown',
                    'status_message' => $status['message'] ?? '',
                    'last_tested_at' => $provider->last_tested_at ? $provider->last_tested_at->format('Y-m-d H:i:s') : null,
                    'last_test_status' => $provider->last_test_status,
                    'last_test_message' => $provider->last_test_message,
                    // Email fields
                    'mailer_type' => $provider->mailer_type,
                    'mail_host' => $provider->mail_host,
                    'mail_port' => $provider->mail_port,
                    'mail_username' => $provider->mail_username,
                    'mail_encryption' => $provider->mail_encryption,
                    'mail_from_address' => $provider->mail_from_address,
                    'mail_from_name' => $provider->mail_from_name,
                    // SMS fields
                    'sms_username' => $provider->sms_username,
                    'sms_from' => $provider->sms_from,
                    'sms_url' => $provider->sms_url,
                    'created_at' => $provider->created_at ? $provider->created_at->format('Y-m-d H:i:s') : null,
                ];
            });
            
            return response()->json([
                'success' => true,
                'providers' => $providers,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading notification providers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $errorMessage = 'Error loading providers: ' . $e->getMessage();
            
            // Include more details in development
            if (config('app.debug')) {
                $errorMessage .= ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage(),
                'providers' => []
            ], 500);
        }
    }

    /**
     * Store a new notification provider
     */
    public function storeNotificationProvider(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'priority' => 'integer|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            // Email fields
            'mailer_type' => 'required_if:type,email|nullable|string|in:smtp,sendmail,mailgun,ses',
            'mail_host' => 'required_if:type,email|nullable|string|max:255',
            'mail_port' => 'required_if:type,email|nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required_if:type,email|nullable|string|in:tls,ssl',
            'mail_from_address' => 'required_if:type,email|nullable|email|max:255',
            'mail_from_name' => 'required_if:type,email|nullable|string|max:255',
            // SMS fields
            'sms_username' => 'required_if:type,sms|nullable|string|max:255',
            'sms_password' => 'required_if:type,sms|nullable|string|max:255',
            'sms_from' => 'required_if:type,sms|nullable|string|max:100',
            'sms_url' => 'required_if:type,sms|nullable|url|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            $provider = NotificationProvider::create([
                'name' => $request->name,
                'type' => $request->type,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
                'is_primary' => $request->has('is_primary') ? (bool)$request->is_primary : false,
                'priority' => $request->priority ?? 0,
                'description' => $request->description,
                // Email fields
                'mailer_type' => $request->mailer_type,
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                'mail_password' => $request->mail_password,
                'mail_encryption' => $request->mail_encryption,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
                // SMS fields
                'sms_username' => $request->sms_username,
                'sms_password' => $request->sms_password,
                'sms_from' => $request->sms_from,
                'sms_url' => $request->sms_url,
            ]);
            
            // If set as primary, unset others
            if ($provider->is_primary) {
                $provider->setAsPrimary();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification provider created successfully',
                'provider' => $provider,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a notification provider
     */
    public function updateNotificationProvider(Request $request, NotificationProvider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'priority' => 'integer|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            // Email fields
            'mailer_type' => 'required_if:type,email|nullable|string|in:smtp,sendmail,mailgun,ses',
            'mail_host' => 'required_if:type,email|nullable|string|max:255',
            'mail_port' => 'required_if:type,email|nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required_if:type,email|nullable|string|in:tls,ssl',
            'mail_from_address' => 'required_if:type,email|nullable|email|max:255',
            'mail_from_name' => 'required_if:type,email|nullable|string|max:255',
            // SMS fields
            'sms_username' => 'required_if:type,sms|nullable|string|max:255',
            'sms_password' => 'required_if:type,sms|nullable|string|max:255',
            'sms_from' => 'required_if:type,sms|nullable|string|max:100',
            'sms_url' => 'required_if:type,sms|nullable|url|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            $wasPrimary = $provider->is_primary;
            
            $provider->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : $provider->is_active,
                'is_primary' => $request->has('is_primary') ? (bool)$request->is_primary : $provider->is_primary,
                'priority' => $request->priority ?? $provider->priority,
                'description' => $request->description,
                // Email fields - only update if provided
                'mailer_type' => $request->has('mailer_type') ? $request->mailer_type : $provider->mailer_type,
                'mail_host' => $request->has('mail_host') ? $request->mail_host : $provider->mail_host,
                'mail_port' => $request->has('mail_port') ? $request->mail_port : $provider->mail_port,
                'mail_username' => $request->has('mail_username') ? $request->mail_username : $provider->mail_username,
                'mail_password' => $request->has('mail_password') && $request->mail_password ? $request->mail_password : $provider->mail_password,
                'mail_encryption' => $request->has('mail_encryption') ? $request->mail_encryption : $provider->mail_encryption,
                'mail_from_address' => $request->has('mail_from_address') ? $request->mail_from_address : $provider->mail_from_address,
                'mail_from_name' => $request->has('mail_from_name') ? $request->mail_from_name : $provider->mail_from_name,
                // SMS fields - only update if provided
                'sms_username' => $request->has('sms_username') ? $request->sms_username : $provider->sms_username,
                'sms_password' => $request->has('sms_password') && $request->sms_password ? $request->sms_password : $provider->sms_password,
                'sms_from' => $request->has('sms_from') ? $request->sms_from : $provider->sms_from,
                'sms_url' => $request->has('sms_url') ? $request->sms_url : $provider->sms_url,
            ]);
            
            // If set as primary, unset others
            if ($provider->is_primary && !$wasPrimary) {
                $provider->setAsPrimary();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification provider updated successfully',
                'provider' => $provider->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification provider
     */
    public function deleteNotificationProvider(NotificationProvider $provider)
    {
        try {
            if ($provider->is_primary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete primary provider. Please set another provider as primary first.'
                ], 403);
            }
            
            $provider->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification provider deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set provider as primary
     */
    public function setPrimaryProvider(NotificationProvider $provider)
    {
        try {
            $provider->setAsPrimary();
            
            return response()->json([
                'success' => true,
                'message' => 'Provider set as primary successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting primary provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single notification provider
     */
    public function getNotificationProvider(NotificationProvider $provider)
    {
        return response()->json([
            'success' => true,
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'type' => $provider->type,
                'is_active' => $provider->is_active,
                'is_primary' => $provider->is_primary,
                'priority' => $provider->priority,
                'description' => $provider->description,
                'mailer_type' => $provider->mailer_type,
                'mail_host' => $provider->mail_host,
                'mail_port' => $provider->mail_port,
                'mail_username' => $provider->mail_username,
                'mail_encryption' => $provider->mail_encryption,
                'mail_from_address' => $provider->mail_from_address,
                'mail_from_name' => $provider->mail_from_name,
                'sms_username' => $provider->sms_username,
                'sms_from' => $provider->sms_from,
                'sms_url' => $provider->sms_url,
                'last_tested_at' => $provider->last_tested_at ? $provider->last_tested_at->format('Y-m-d H:i:s') : null,
                'last_test_status' => $provider->last_test_status,
                'last_test_message' => $provider->last_test_message,
            ]
        ]);
    }

    /**
     * Test notification provider
     */
    public function testNotificationProvider(Request $request, NotificationProvider $provider)
    {
        try {
            // Validate based on provider type
            if ($provider->type === 'email') {
                $request->validate([
                    'test_email' => 'required|email',
                ]);
            } else {
                $request->validate([
                    'test_phone' => 'required|string|min:9',
                ]);
            }

            if ($provider->type === 'email') {
                $result = $provider->testEmail($request->test_email);
            } else {
                // Clean phone number for SMS
                $phone = preg_replace('/[^0-9]/', '', $request->test_phone);
                if (!str_starts_with($phone, '255')) {
                    $phone = '255' . ltrim($phone, '0');
                }
                $result = $provider->testSMS($phone);
            }
            
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors()['test_' . ($provider->type === 'email' ? 'email' : 'phone')] ?? ['Invalid input']),
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Test notification provider failed', [
                'provider_id' => $provider->id,
                'provider_type' => $provider->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
