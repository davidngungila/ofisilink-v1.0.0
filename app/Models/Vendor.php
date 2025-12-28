<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'vendor_code', 'name', 'contact_person', 'email', 'phone', 'mobile',
        'address', 'city', 'country', 'tax_id', 'account_id', 'credit_limit',
        'payment_terms', 'payment_terms_days', 'is_active', 'notes',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'vendor_id');
    }

    // Helper methods
    public function getTotalOutstandingAttribute()
    {
        return $this->bills()
            ->whereIn('status', ['Pending', 'Partially Paid'])
            ->sum('balance');
    }

    public function getOverdueAmountAttribute()
    {
        return $this->bills()
            ->whereIn('status', ['Pending', 'Partially Paid'])
            ->whereDate('due_date', '<', now())
            ->sum('balance');
    }

    public static function generateCode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $sequence = $last ? ((int) substr($last->vendor_code, 3)) + 1 : 1;
        return 'VEN' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}

