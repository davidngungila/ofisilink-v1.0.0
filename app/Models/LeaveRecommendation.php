<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_request_id',
        'recommended_by',
        'recommendation',
        'comments',
        'status',
        'recommended_start_date',
        'recommended_end_date',
        'financial_year',
        'notes',
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the leave request
     */
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    /**
     * Get the user who made the recommendation
     */
    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }
}