<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location_id',
        'department_id',
        'start_time',
        'end_time',
        'work_hours',
        'break_duration_minutes',
        'break_start_time',
        'break_end_time',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'overtime_threshold_minutes',
        'working_days',
        'is_flexible',
        'flexible_start_min',
        'flexible_start_max',
        'is_active',
        'effective_from',
        'effective_to',
        'holidays',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'flexible_start_min' => 'datetime',
        'flexible_start_max' => 'datetime',
        'is_flexible' => 'boolean',
        'is_active' => 'boolean',
        'working_days' => 'array',
        'holidays' => 'array',
        'settings' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the location
     */
    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get attendances using this schedule
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }

    /**
     * Check if a day is a working day
     */
    public function isWorkingDay($dayOfWeek)
    {
        if (!$this->working_days) {
            return true; // If not specified, all days are working days
        }
        return in_array($dayOfWeek, $this->working_days);
    }

    /**
     * Check if date is a holiday
     */
    public function isHoliday($date)
    {
        if (!$this->holidays) {
            return false;
        }
        $dateStr = is_string($date) ? $date : $date->format('Y-m-d');
        return in_array($dateStr, $this->holidays);
    }

    /**
     * Get expected time in for a date
     */
    public function getExpectedTimeIn($date = null)
    {
        if ($this->is_flexible && $this->flexible_start_min) {
            return $this->flexible_start_min;
        }
        return $this->start_time;
    }

    /**
     * Get expected time out for a date
     */
    public function getExpectedTimeOut($date = null)
    {
        return $this->end_time;
    }
}
