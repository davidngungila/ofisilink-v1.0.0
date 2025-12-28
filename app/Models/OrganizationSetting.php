<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrganizationSetting extends Model
{
    protected $table = 'organization_settings';
    
    protected $fillable = [
        'company_name', 'company_registration_number', 'company_tax_id',
        'company_address', 'company_city', 'company_state', 'company_country', 'company_postal_code',
        'company_phone', 'company_email', 'company_website',
        'company_logo', 'company_favicon',
        'currency', 'currency_symbol', 'currency_position', 'decimal_places', 'number_format',
        'financial_year_start_month', 'financial_year_start_day',
        'current_financial_year', 'financial_year_start_date', 'financial_year_end_date',
        'financial_year_locked', 'financial_year_history',
        'timezone', 'date_format', 'time_format', 'week_start_day', 'first_day_of_month',
        'locale', 'country_code', 'language',
        'max_file_size', 'allowed_file_types',
        'email_notifications_enabled', 'sms_notifications_enabled', 'push_notifications_enabled',
        'business_hours_start', 'business_hours_end', 'business_days',
        'payroll_period_days', 'payroll_processing_day', 'payroll_currency',
        'default_annual_leave_days', 'default_sick_leave_days', 'max_consecutive_leave_days',
        'vat_rate', 'income_tax_rate', 'tax_inclusive_pricing',
        'custom_fields', 'integration_settings', 'internal_notes',
        'updated_by',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'current_financial_year' => 'integer',
        'financial_year_start_date' => 'date',
        'financial_year_end_date' => 'date',
        'financial_year_locked' => 'boolean',
        'financial_year_history' => 'array',
        'business_days' => 'array',
        'business_hours_start' => 'datetime',
        'business_hours_end' => 'datetime',
        'max_file_size' => 'integer',
        'email_notifications_enabled' => 'boolean',
        'sms_notifications_enabled' => 'boolean',
        'push_notifications_enabled' => 'boolean',
        'payroll_period_days' => 'integer',
        'payroll_processing_day' => 'integer',
        'default_annual_leave_days' => 'integer',
        'default_sick_leave_days' => 'integer',
        'max_consecutive_leave_days' => 'integer',
        'vat_rate' => 'decimal:2',
        'income_tax_rate' => 'decimal:2',
        'tax_inclusive_pricing' => 'boolean',
        'custom_fields' => 'array',
        'integration_settings' => 'array',
        'maintenance_mode' => 'boolean',
    ];

    // Singleton pattern - always return the same instance
    public static function getSettings()
    {
        $settings = static::firstOrCreate(['id' => 1], [
            'timezone' => 'Africa/Dar_es_Salaam',
            'country_code' => 'TZ',
            'currency' => 'TZS',
            'currency_symbol' => 'TSh',
        ]);
        
        // Ensure timezone is always set to Tanzania
        if (empty($settings->timezone) || $settings->timezone === 'UTC') {
            $settings->timezone = 'Africa/Dar_es_Salaam';
            $settings->save();
        }
        
        return $settings;
    }

    // Get current financial year dates
    public function getFinancialYearDates($year = null)
    {
        $year = $year ?? $this->current_financial_year;
        $month = (int) $this->financial_year_start_month;
        $day = (int) $this->financial_year_start_day;
        
        $startDate = Carbon::create($year, $month, $day);
        $endDate = $startDate->copy()->addYear()->subDay();
        
        return [
            'start' => $startDate,
            'end' => $endDate,
            'year' => $year,
        ];
    }

    // Check if a date falls within current financial year
    public function isInCurrentFinancialYear($date)
    {
        $dates = $this->getFinancialYearDates();
        $checkDate = Carbon::parse($date);
        
        return $checkDate->gte($dates['start']) && $checkDate->lte($dates['end']);
    }

    // Get financial year for a given date
    public function getFinancialYearForDate($date)
    {
        $date = Carbon::parse($date);
        $month = (int) $this->financial_year_start_month;
        $day = (int) $this->financial_year_start_day;
        $fyStart = Carbon::create($date->year, $month, $day);
        
        if ($date->lt($fyStart)) {
            return $date->year - 1;
        }
        
        return $date->year;
    }

    // Initialize financial year dates
    public function initializeFinancialYear($year = null)
    {
        $year = $year ?? $this->current_financial_year;
        $dates = $this->getFinancialYearDates($year);
        
        $this->update([
            'financial_year_start_date' => $dates['start'],
            'financial_year_end_date' => $dates['end'],
        ]);
        
        return $dates;
    }

    // Format currency
    public function formatCurrency($amount)
    {
        $formatted = number_format($amount, $this->decimal_places);
        
        if ($this->currency_position === 'prefix') {
            return $this->currency_symbol . ' ' . $formatted;
        }
        
        return $formatted . ' ' . $this->currency_symbol;
    }

    // Format date according to organization format
    public function formatDate($date)
    {
        return Carbon::parse($date)->format($this->date_format);
    }

    // Format datetime
    public function formatDateTime($datetime)
    {
        return Carbon::parse($datetime)->format($this->date_format . ' ' . $this->time_format);
    }

    // Relationships
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}






