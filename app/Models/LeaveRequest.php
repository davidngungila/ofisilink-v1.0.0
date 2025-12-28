<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'leave_location',
        'status',
        'hr_officer_comments',
        'comments',
        'reviewed_by',
        'reviewed_at',
        'approval_letter_number',
        'approval_date',
        'leave_certificate_number',
        'fare_certificate_number',
        'fare_approved_amount',
        'payment_voucher_number',
        'payment_date',
        'hr_processing_notes',
        'documents_processed_by',
        'documents_processed_at',
        'actual_return_date',
        'health_status',
        'work_readiness',
        'return_comments',
        'resumption_certificate_path',
        'return_submitted_at',
        'total_fare_approved',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
        'documents_processed_at' => 'datetime',
        'return_submitted_at' => 'datetime',
        'approval_date' => 'date',
        'payment_date' => 'date',
        'actual_return_date' => 'date',
        'total_days' => 'integer',
        'fare_approved_amount' => 'decimal:2',
        'total_fare_approved' => 'decimal:2',
    ];

    /**
     * Get the user who made the request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the employee who made the request (alias for user)
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
     * Get the user who reviewed the request
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who processed documents
     */
    public function documentProcessor()
    {
        return $this->belongsTo(User::class, 'documents_processed_by');
    }

    /**
     * Get the dependents for this leave request
     */
    public function dependents()
    {
        return $this->hasMany(LeaveDependent::class, 'leave_request_id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval']);
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved_pending_docs', 'on_leave', 'completed']);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['rejected', 'rejected_for_edit']);
    }

    /**
     * Scope for cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}