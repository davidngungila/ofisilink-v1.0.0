<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
        'require_gps',
        'allow_remote',
        'allowed_methods',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'require_gps' => 'boolean',
        'allow_remote' => 'boolean',
        'allowed_methods' => 'array',
        'settings' => 'array',
    ];

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
     * Get devices at this location
     */
    public function devices()
    {
        return $this->hasMany(AttendanceDevice::class, 'location_id');
    }

    /**
     * Get attendances at this location
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'location_id');
    }

    /**
     * Get work schedules for this location
     */
    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class, 'location_id');
    }

    /**
     * Get policies for this location
     */
    public function policies()
    {
        return $this->hasMany(AttendancePolicy::class, 'location_id');
    }

    /**
     * Check if method is allowed at this location
     */
    public function isMethodAllowed($method)
    {
        if (!$this->allowed_methods) {
            return true; // If not specified, allow all
        }
        return in_array($method, $this->allowed_methods);
    }

    /**
     * Check if GPS coordinates are within allowed radius
     */
    public function isWithinRadius($lat, $lng)
    {
        if (!$this->latitude || !$this->longitude) {
            return true; // If location doesn't have coordinates, allow
        }

        $distance = $this->calculateDistance($this->latitude, $this->longitude, $lat, $lng);
        return $distance <= $this->radius_meters;
    }

    /**
     * Calculate distance between two coordinates in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
