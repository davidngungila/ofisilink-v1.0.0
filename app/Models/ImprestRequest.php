<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImprestRequest extends Model
{
    protected $fillable = [
        'request_no',
        'accountant_id',
        'purpose',
        'amount',
        'expected_return_date',
        'priority',
        'description',
        'status',
        'hod_approved_at',
        'hod_approved_by',
        'ceo_approved_at',
        'ceo_approved_by',
        'paid_at',
        'payment_method',
        'payment_reference',
        'payment_notes',
        'completed_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'expected_return_date' => 'date',
        'hod_approved_at' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function assignments()
    {
        return $this->hasMany(ImprestAssignment::class);
    }

    public function receipts()
    {
        return $this->hasManyThrough(
            ImprestReceipt::class, 
            ImprestAssignment::class,
            'imprest_request_id', // Foreign key on imprest_assignments table
            'assignment_id', // Foreign key on imprest_receipts table
            'id', // Local key on imprest_requests table
            'id' // Local key on imprest_assignments table
        );
    }

    public function hodApproval()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function ceoApproval()
    {
        return $this->belongsTo(User::class, 'ceo_approved_by');
    }

    // Accessors
    public function getAssignedStaffCountAttribute()
    {
        return $this->assignments()->count();
    }

    public function getProgressPercentageAttribute()
    {
        switch ($this->status) {
            case 'pending_hod':
                return 20;
            case 'pending_ceo':
                return 40;
            case 'approved':
                return 60;
            case 'assigned':
                return 70;
            case 'paid':
                return 80;
            case 'pending_receipt_verification':
                return 90;
            case 'completed':
                return 100;
            default:
                return 0;
        }
    }

    // Scopes
    public function scopePendingHod($query)
    {
        return $query->where('status', 'pending_hod');
    }

    public function scopePendingCeo($query)
    {
        return $query->where('status', 'pending_ceo');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
