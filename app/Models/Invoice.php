<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no', 'customer_id', 'invoice_date', 'due_date', 'reference_no',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'balance', 'status', 'notes', 'terms',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id');
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->status === 'Overdue' || 
               ($this->due_date < now() && $this->balance > 0);
    }

    public function updateStatus(): void
    {
        // Don't update status if it's pending approval, rejected, or draft
        if (in_array($this->status, ['Pending for Approval', 'Rejected', 'Draft'])) {
            return;
        }
        
        if ($this->balance <= 0) {
            $this->status = 'Paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'Partially Paid';
        } elseif ($this->due_date < now() && $this->balance > 0) {
            $this->status = 'Overdue';
        } elseif ($this->status === 'Approved') {
            $this->status = 'Sent';
        } elseif (!in_array($this->status, ['Sent', 'Partially Paid', 'Overdue', 'Paid'])) {
            $this->status = 'Sent';
        }
        $this->save();
    }

    public static function generateInvoiceNo(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())
            ->where('invoice_no', 'like', "INV{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $sequence = (int) substr($last->invoice_no, -4) + 1;
        } else {
            $sequence = 1;
        }

        return "INV{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}



