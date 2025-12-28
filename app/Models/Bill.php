<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'bill_no', 'vendor_id', 'bill_date', 'due_date', 'reference_no',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'balance', 'status', 'notes', 'terms',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class, 'bill_id');
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->status === 'Overdue' || 
               ($this->due_date < now() && $this->balance > 0);
    }

    public function updateStatus(): void
    {
        if ($this->balance <= 0) {
            $this->status = 'Paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'Partially Paid';
        } elseif ($this->due_date < now() && $this->balance > 0) {
            $this->status = 'Overdue';
        } else {
            $this->status = 'Pending';
        }
        $this->save();
    }

    public static function generateBillNo(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())
            ->where('bill_no', 'like', "BL{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $sequence = (int) substr($last->bill_no, -4) + 1;
        } else {
            $sequence = 1;
        }

        return "BL{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}



