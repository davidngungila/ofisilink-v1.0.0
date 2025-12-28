<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

class IncidentEmailConfig extends Model
{
    use HasFactory;

    protected $table = 'incident_email_config';

    protected $fillable = [
        'email_address',
        'protocol',
        'host',
        'port',
        'username',
        'password',
        'ssl_enabled',
        'folder',
        'is_active',
        'connection_status',
        'last_connection_test_at',
        'connection_error',
        'last_sync_at',
        'sync_count',
        'failed_sync_count',
        'sync_settings'
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_connection_test_at' => 'datetime',
        'sync_settings' => 'array',
    ];

    /**
     * Encrypt password when setting
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt password when getting
     */
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get active email configurations
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }
}



