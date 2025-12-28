<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location_id',
        'department_id',
        'require_approval_for_late',
        'require_approval_for_early_leave',
        'require_approval_for_overtime',
        'allow_remote_attendance',
        'max_remote_days_per_month',
        'auto_approve_verified',
        'require_photo_for_manual',
        'require_location_for_mobile',
        'max_late_minutes_per_month',
        'max_early_leave_minutes_per_month',
        'allowed_attendance_methods',
        'penalty_rules',
        'reward_rules',
        'notification_settings',
        'approval_workflow',
        'is_active',
        'effective_from',
        'effective_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'require_approval_for_late' => 'boolean',
        'require_approval_for_early_leave' => 'boolean',
        'require_approval_for_overtime' => 'boolean',
        'allow_remote_attendance' => 'boolean',
        'auto_approve_verified' => 'boolean',
        'require_photo_for_manual' => 'boolean',
        'require_location_for_mobile' => 'boolean',
        'is_active' => 'boolean',
        'allowed_attendance_methods' => 'array',
        'penalty_rules' => 'array',
        'reward_rules' => 'array',
        'notification_settings' => 'array',
        'approval_workflow' => 'array',
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
     * Check if method is allowed
     */
    public function isMethodAllowed($method)
    {
        if (!$this->allowed_attendance_methods) {
            return true; // If not specified, allow all
        }
        return in_array($method, $this->allowed_attendance_methods);
    }

    /**
     * Check if approval is required for late
     */
    public function requiresApprovalForLate()
    {
        return $this->require_approval_for_late;
    }

    /**
     * Check if approval is required for early leave
     */
    public function requiresApprovalForEarlyLeave()
    {
        return $this->require_approval_for_early_leave;
    }

    /**
     * Check if approval is required for overtime
     */
    public function requiresApprovalForOvertime()
    {
        return $this->require_approval_for_overtime;
    }
}
