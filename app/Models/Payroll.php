<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ChartOfAccount;
use App\Models\CashBox;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_number',
        'pay_period',
        'pay_date',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'allowances',
        'deductions',
        'total_amount',
        'status',
        'processed_by',
        'reviewed_by',
        'approved_by',
        'paid_by',
        'reviewed_at',
        'approved_at',
        'paid_at',
        'review_notes',
        'approval_notes',
        'payment_method',
        'payment_date',
        'transaction_reference',
        'gl_account_id',
        'cash_box_id',
        'transaction_details',
    ];

    protected $casts = [
        'pay_date' => 'date',
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_date' => 'date',
    ];

    /**
     * Get the payroll items for this payroll
     */
    public function items()
    {
        return $this->hasMany(PayrollItem::class, 'payroll_id');
    }

    /**
     * Get the user (employee) for this payroll
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who processed this payroll
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the user who reviewed this payroll
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved this payroll
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who paid this payroll
     */
    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Alternative method names used in controller
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Get the GL account used for payment
     */
    public function glAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'gl_account_id');
    }

    /**
     * Get the cash box used for cash payment
     */
    public function cashBox()
    {
        return $this->belongsTo(CashBox::class, 'cash_box_id');
    }

    /**
     * Scope for pending payrolls
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved payrolls
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid payrolls
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}