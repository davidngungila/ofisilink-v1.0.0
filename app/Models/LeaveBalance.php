<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'financial_year',
        'total_days_allotted',
        'days_taken',
        'carry_forward_days',
    ];

    protected $casts = [
        'financial_year' => 'integer',
        'total_days_allotted' => 'integer',
        'days_taken' => 'integer',
        'carry_forward_days' => 'integer',
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the leave type
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get remaining days attribute
     */
    public function getRemainingDaysAttribute()
    {
        return ($this->total_days_allotted ?? 0) + ($this->carry_forward_days ?? 0) - ($this->days_taken ?? 0);
    }
}