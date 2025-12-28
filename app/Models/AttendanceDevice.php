<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AttendanceDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_id',
        'device_type',
        'model',
        'manufacturer',
        'serial_number',
        'location_id',
        'ip_address',
        'mac_address',
        'port',
        'connection_type',
        'connection_config',
        'is_active',
        'is_online',
        'last_sync_at',
        'sync_interval_minutes',
        'capabilities',
        'settings',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_sync_at' => 'datetime',
        'connection_config' => 'array',
        'capabilities' => 'array',
        'settings' => 'array',
    ];

    // Device types
    const TYPE_BIOMETRIC = 'biometric';
    const TYPE_RFID = 'rfid';
    const TYPE_FINGERPRINT = 'fingerprint';
    const TYPE_FACE_RECOGNITION = 'face_recognition';
    const TYPE_CARD_SWIPE = 'card_swipe';
    const TYPE_MOBILE = 'mobile';

    // Connection types
    const CONNECTION_NETWORK = 'network';
    const CONNECTION_USB = 'usb';
    const CONNECTION_BLUETOOTH = 'bluetooth';
    const CONNECTION_WIFI = 'wifi';

    /**
     * Get the location
     */
    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get attendances recorded by this device
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'device_id');
    }

    /**
     * Check if device needs sync
     */
    public function needsSync()
    {
        if (!$this->last_sync_at) {
            return true;
        }
        return Carbon::now()->diffInMinutes($this->last_sync_at) >= $this->sync_interval_minutes;
    }

    /**
     * Mark device as online
     */
    public function markOnline()
    {
        $this->update([
            'is_online' => true,
            'last_sync_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark device as offline
     */
    public function markOffline()
    {
        $this->update([
            'is_online' => false,
        ]);
    }

    /**
     * Get device types
     * Only Biometric (ZKTeco UF200-S) is supported
     */
    public static function getDeviceTypes()
    {
        return [
            self::TYPE_BIOMETRIC => 'ZKTeco UF200-S Biometric',
        ];
    }
}
