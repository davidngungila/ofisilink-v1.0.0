<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_days_per_year',
        'requires_approval',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'max_days_per_year' => 'integer',
        'requires_approval' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get leave requests of this type
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id', 'id');
    }
}