<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get setting by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? static::castValue($setting->value, $setting->type) : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value, $type = 'text', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description ?? static::where('key', $key)->first()?->description
            ]
        );
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int', 'number' => (int) $value,
            'float', 'decimal' => (float) $value,
            'json', 'array' => json_decode($value, true),
            'date' => $value,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ?: $value,
            'url' => filter_var($value, FILTER_VALIDATE_URL) ?: $value,
            default => (string) $value,
        };
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray()
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        })->toArray();
    }

    /**
     * Get settings by type
     */
    public static function getByType($type)
    {
        return static::where('type', $type)->get();
    }
}
