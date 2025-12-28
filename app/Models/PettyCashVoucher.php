<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PettyCashVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'date',
        'payee',
        'purpose',
        'amount',
        'status',
        'gl_account_id',
        'cash_box_id',
        'created_by',
        'accountant_id',
        'hod_id',
        'ceo_id',
        'paid_by',
        'accountant_verified_at',
        'hod_approved_at',
        'ceo_approved_at',
        'paid_at',
        'retired_at',
        'accountant_comments',
        'hod_comments',
        'ceo_comments',
        'retirement_comments',
        'attachments',
        'retirement_receipts',
        'payment_method',
        'paid_amount',
        'payment_currency',
        'bank_name',
        'account_number',
        'payment_reference',
        'payment_notes',
        'payment_attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'accountant_verified_at' => 'datetime',
        'hod_approved_at' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'retired_at' => 'datetime',
        'attachments' => 'array',
        'retirement_receipts' => 'array',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Get the user who created this voucher
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the accountant who verified this voucher
     */
    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    /**
     * Get the HOD who approved this voucher
     */
    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    /**
     * Get the CEO who approved this voucher
     */
    public function ceo()
    {
        return $this->belongsTo(User::class, 'ceo_id');
    }

    /**
     * Get the user who paid this voucher
     */
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Alias for creator (backward compatibility)
     */
    public function user()
    {
        return $this->creator();
    }

    /**
     * Get voucher lines
     */
    public function lines()
    {
        return $this->hasMany(PettyCashVoucherLine::class, 'voucher_id');
    }

    /**
     * Scope for pending vouchers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved vouchers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected vouchers
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the voucher can be deleted
     * Vouchers can only be deleted if they are pending accountant review
     * Once they move to any other stage, they cannot be deleted
     */
    public function canBeDeleted()
    {
        return $this->status === 'pending_accountant' && 
               $this->accountant_id === null &&
               $this->accountant_verified_at === null;
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending_accountant' => 'warning',
            'pending_hod' => 'info',
            'pending_ceo' => 'primary',
            'approved_for_payment' => 'success',
            'paid' => 'success',
            'pending_retirement_review' => 'warning',
            'retired' => 'secondary',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get progress percentage based on status
     */
    public function getProgressPercentageAttribute()
    {
        return match($this->status) {
            'pending_accountant' => 12.5,
            'pending_hod' => 37.5,
            'pending_ceo' => 62.5,
            'approved_for_payment' => 75,
            'paid' => 87.5,
            'pending_retirement_review' => 90,
            'retired' => 100,
            'rejected' => 0,
            default => 0,
        };
    }
}