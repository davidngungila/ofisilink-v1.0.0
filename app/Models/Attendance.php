<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'enroll_id',
        'attendance_date',
        'time_in',
        'time_out',
        'check_in_time',
        'check_out_time',
        'punch_time',
        'break_start',
        'break_end',
        'total_hours',
        'break_duration',
        'attendance_method',
        'device_id',
        'device_type',
        'device_ip',
        'attendance_device_id',
        'location_id',
        'schedule_id',
        'location',
        'location_name',
        'latitude',
        'longitude',
        'ip_address',
        'status',
        'status_code',
        'verify_mode',
        'is_late',
        'is_early_leave',
        'is_overtime',
        'notes',
        'remarks',
        'approved_by',
        'approved_at',
        'verification_status',
        'metadata',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'punch_time' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'is_late' => 'boolean',
        'is_early_leave' => 'boolean',
        'is_overtime' => 'boolean',
        'approved_at' => 'datetime',
        'metadata' => 'array',
        'total_hours' => 'integer',
        'break_duration' => 'integer',
        'status_code' => 'integer',
    ];

    // Attendance methods constants
    const METHOD_MANUAL = 'manual';
    const METHOD_BIOMETRIC = 'biometric';
    const METHOD_MOBILE_APP = 'mobile_app';
    const METHOD_RFID = 'rfid';
    const METHOD_FACE_RECOGNITION = 'face_recognition';
    const METHOD_FINGERPRINT = 'fingerprint';
    const METHOD_CARD_SWIPE = 'card_swipe';

    // Status constants
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_EARLY_LEAVE = 'early_leave';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_ON_LEAVE = 'on_leave';

    // Verification status constants
    const VERIFICATION_PENDING = 'pending';
    const VERIFICATION_VERIFIED = 'verified';
    const VERIFICATION_REJECTED = 'rejected';

    /**
     * Get the user associated with this attendance
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the employee record
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the approver (HR/Manager)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the location
     */
    public function attendanceLocation()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    /**
     * Get the device
     */
    public function attendanceDevice()
    {
        return $this->belongsTo(AttendanceDevice::class, 'attendance_device_id');
    }

    /**
     * Get the work schedule
     */
    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'schedule_id');
    }

    /**
     * Calculate total working hours
     */
    public function calculateTotalHours()
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        // Extract time string from time_in (handle datetime cast)
        $timeInValue = $this->time_in;
        if ($timeInValue instanceof Carbon) {
            $timeInStr = $timeInValue->format('H:i:s');
        } elseif (is_string($timeInValue) && strpos($timeInValue, ' ') !== false) {
            // It's a datetime string, extract just the time part
            $timeInStr = Carbon::parse($timeInValue)->format('H:i:s');
        } else {
            // It's already a time string
            $timeInStr = $timeInValue;
        }

        // Extract time string from time_out (handle datetime cast)
        $timeOutValue = $this->time_out;
        if ($timeOutValue instanceof Carbon) {
            $timeOutStr = $timeOutValue->format('H:i:s');
        } elseif (is_string($timeOutValue) && strpos($timeOutValue, ' ') !== false) {
            // It's a datetime string, extract just the time part
            $timeOutStr = Carbon::parse($timeOutValue)->format('H:i:s');
        } else {
            // It's already a time string
            $timeOutStr = $timeOutValue;
        }

        $timeIn = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $timeInStr);
        $timeOut = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $timeOutStr);
        
        // Handle overnight shifts (time_out is next day)
        if ($timeOut->lt($timeIn)) {
            // If time_out is before time_in, assume it's next day
            $timeOut->addDay();
        }
        
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        
        // Subtract break duration if exists (but ensure it doesn't exceed total time)
        if ($this->break_duration && $this->break_duration > 0) {
            // Only subtract break if it's less than or equal to total time
            if ($this->break_duration <= $totalMinutes) {
                $totalMinutes -= $this->break_duration;
            } else {
                // If break duration is greater than total time, log warning and don't subtract
                \Log::warning('Break duration exceeds total time worked', [
                    'attendance_id' => $this->id,
                    'total_minutes' => $totalMinutes,
                    'break_duration' => $this->break_duration
                ]);
                // Don't subtract - return total time without break
            }
        }
        
        // Ensure result is never negative (return 0 if calculation error)
        return max(0, $totalMinutes);
    }

    /**
     * Check if employee is late
     */
    public function checkLate($expectedTimeIn = '09:00:00')
    {
        if (!$this->time_in) {
            return false;
        }

        // Handle time_in which might be a Carbon instance, datetime string, or time string
        $timeInValue = $this->time_in;
        if ($timeInValue instanceof Carbon) {
            $timeInStr = $timeInValue->format('H:i:s');
        } elseif (is_string($timeInValue) && strpos($timeInValue, ' ') !== false) {
            // It's a datetime string, extract just the time part
            $timeInStr = Carbon::parse($timeInValue)->format('H:i:s');
        } else {
            // It's already a time string
            $timeInStr = $timeInValue;
        }

        $expected = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $expectedTimeIn);
        $actual = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $timeInStr);
        
        return $actual->gt($expected);
    }

    /**
     * Get formatted total hours
     */
    public function getFormattedTotalHoursAttribute()
    {
        if (!$this->total_hours || $this->total_hours < 0) {
            return '0:00';
        }

        $hours = floor($this->total_hours / 60);
        $minutes = abs($this->total_hours % 60);
        
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Get time_in as time string
     */
    public function getTimeInStringAttribute()
    {
        if (!$this->time_in) {
            return null;
        }
        
        $timeInValue = $this->time_in;
        if ($timeInValue instanceof Carbon) {
            return $timeInValue->format('H:i:s');
        } elseif (is_string($timeInValue) && strpos($timeInValue, ' ') !== false) {
            return Carbon::parse($timeInValue)->format('H:i:s');
        } else {
            return $timeInValue;
        }
    }

    /**
     * Get time_out as time string
     */
    public function getTimeOutStringAttribute()
    {
        if (!$this->time_out) {
            return null;
        }
        
        $timeOutValue = $this->time_out;
        if ($timeOutValue instanceof Carbon) {
            return $timeOutValue->format('H:i:s');
        } elseif (is_string($timeOutValue) && strpos($timeOutValue, ' ') !== false) {
            return Carbon::parse($timeOutValue)->format('H:i:s');
        } else {
            return $timeOutValue;
        }
    }

    /**
     * Get break_start as time string
     */
    public function getBreakStartStringAttribute()
    {
        if (!$this->break_start) {
            return null;
        }
        
        $breakStartValue = $this->break_start;
        if ($breakStartValue instanceof Carbon) {
            return $breakStartValue->format('H:i:s');
        } elseif (is_string($breakStartValue) && strpos($breakStartValue, ' ') !== false) {
            return Carbon::parse($breakStartValue)->format('H:i:s');
        } else {
            return $breakStartValue;
        }
    }

    /**
     * Get break_end as time string
     */
    public function getBreakEndStringAttribute()
    {
        if (!$this->break_end) {
            return null;
        }
        
        $breakEndValue = $this->break_end;
        if ($breakEndValue instanceof Carbon) {
            return $breakEndValue->format('H:i:s');
        } elseif (is_string($breakEndValue) && strpos($breakEndValue, ' ') !== false) {
            return Carbon::parse($breakEndValue)->format('H:i:s');
        } else {
            return $breakEndValue;
        }
    }

    /**
     * Get all available attendance methods
     * Only Manual and Biometric (ZKTeco UF200-S) are supported
     */
    public static function getAttendanceMethods()
    {
        return [
            self::METHOD_MANUAL => 'Manual Entry',
            self::METHOD_BIOMETRIC => 'ZKTeco UF200-S Biometric',
        ];
    }

    /**
     * Scope: Get attendances for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get attendances by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('attendance_method', $method);
    }

    /**
     * Scope: Get attendances by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get late attendances
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope: Get verified attendances
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::VERIFICATION_VERIFIED);
    }
}

