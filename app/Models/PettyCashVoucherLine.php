<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PettyCashVoucherLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'description',
        'qty',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the voucher this line belongs to
     */
    public function voucher()
    {
        return $this->belongsTo(PettyCashVoucher::class, 'voucher_id');
    }
}