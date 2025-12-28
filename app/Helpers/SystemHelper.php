<?php

namespace App\Helpers;

use App\Models\OrganizationSetting;
use App\Models\SystemSetting;
use Carbon\Carbon;

class SystemHelper
{
    /**
     * Get currency formatted amount
     */
    public static function formatCurrency($amount)
    {
        $orgSettings = OrganizationSetting::getSettings();
        $formatted = number_format($amount, $orgSettings->decimal_places ?? 2);
        
        if (($orgSettings->currency_position ?? 'prefix') === 'prefix') {
            return ($orgSettings->currency_symbol ?? 'TSh') . ' ' . $formatted;
        }
        
        return $formatted . ' ' . ($orgSettings->currency_symbol ?? 'TSh');
    }

    /**
     * Format date according to organization settings (Tanzania timezone)
     */
    public static function formatDate($date)
    {
        $orgSettings = OrganizationSetting::getSettings();
        $dateFormat = $orgSettings->date_format ?? 'Y-m-d';
        $timezone = self::getTimezone();
        return Carbon::parse($date)->setTimezone($timezone)->format($dateFormat);
    }

    /**
     * Format datetime according to organization settings (Tanzania timezone)
     */
    public static function formatDateTime($datetime)
    {
        $orgSettings = OrganizationSetting::getSettings();
        $dateFormat = $orgSettings->date_format ?? 'Y-m-d';
        $timeFormat = $orgSettings->time_format ?? 'H:i:s';
        $timezone = self::getTimezone();
        return Carbon::parse($datetime)->setTimezone($timezone)->format($dateFormat . ' ' . $timeFormat);
    }

    /**
     * Get max file size from settings
     */
    public static function getMaxFileSize()
    {
        return SystemSetting::getValue('max_file_size', 10) * 1024 * 1024; // Convert MB to bytes
    }

    /**
     * Get allowed file types from settings
     */
    public static function getAllowedFileTypes()
    {
        $types = SystemSetting::getValue('allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx');
        return explode(',', $types);
    }

    /**
     * Get timezone from settings - always defaults to Tanzania
     */
    public static function getTimezone()
    {
        $orgSettings = OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? 'Africa/Dar_es_Salaam';
        
        // Force Tanzania timezone if invalid or UTC
        if (empty($timezone) || $timezone === 'UTC') {
            $timezone = 'Africa/Dar_es_Salaam';
        }
        
        return $timezone;
    }
    
    /**
     * Get current date/time in Tanzania timezone
     */
    public static function now()
    {
        return Carbon::now(self::getTimezone());
    }
    
    /**
     * Parse a date/time string in Tanzania timezone
     */
    public static function parseDate($date)
    {
        return Carbon::parse($date, self::getTimezone());
    }

    /**
     * Get currency code
     */
    public static function getCurrency()
    {
        return SystemSetting::getValue('currency', 'TZS');
    }

    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol()
    {
        return SystemSetting::getValue('currency_symbol', 'TSh');
    }

    /**
     * Get date format
     */
    public static function getDateFormat()
    {
        return SystemSetting::getValue('date_format', 'Y-m-d');
    }

    /**
     * Get time format
     */
    public static function getTimeFormat()
    {
        return SystemSetting::getValue('time_format', 'H:i:s');
    }
}

