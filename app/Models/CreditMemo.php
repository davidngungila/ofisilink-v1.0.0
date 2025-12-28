<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditMemo extends Model
{
    protected $fillable = [
        'memo_no', 'invoice_id', 'customer_id', 'memo_date', 'type',
        'amount', 'reason', 'status', 'created_by'
    ];

    protected $casts = [
        'memo_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateMemoNo(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())
            ->where('memo_no', 'like', "CM{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $sequence = (int) substr($last->memo_no, -4) + 1;
        } else {
            $sequence = 1;
        }

        return "CM{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}



