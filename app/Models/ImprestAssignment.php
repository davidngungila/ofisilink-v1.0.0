<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImprestAssignment extends Model
{
    protected $fillable = [
        'imprest_request_id',
        'staff_id',
        'assigned_amount',
        'assignment_notes',
        'receipt_submitted',
        'receipt_submitted_at',
        'assigned_by',
        'assigned_at',
        'payment_method',
        'payment_date',
        'bank_account_id',
        'bank_name',
        'account_number',
        'payment_reference',
        'payment_notes',
        'paid_amount',
        'paid_at',
        'paid_by'
    ];

    protected $casts = [
        'assigned_amount' => 'decimal:2',
        'receipt_submitted' => 'boolean',
        'receipt_submitted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'payment_date' => 'date',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function imprestRequest()
    {
        return $this->belongsTo(ImprestRequest::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function receipts()
    {
        return $this->hasMany(ImprestReceipt::class, 'assignment_id', 'id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class, 'bank_account_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getIsPaidAttribute()
    {
        return !is_null($this->paid_at) && !is_null($this->paid_amount);
    }
}
