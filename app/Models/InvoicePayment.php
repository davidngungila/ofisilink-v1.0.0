<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'payment_no', 'invoice_id', 'payment_date', 'amount', 'payment_method',
        'reference_no', 'bank_account_id', 'notes', 'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentNo(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())
            ->where('payment_no', 'like', "RP{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $sequence = (int) substr($last->payment_no, -4) + 1;
        } else {
            $sequence = 1;
        }

        return "RP{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}



