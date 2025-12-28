<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetDepreciation extends Model
{
    protected $fillable = [
        'fixed_asset_id', 'depreciation_date', 'period', 'period_type', 'depreciation_amount',
        'accumulated_depreciation_before', 'accumulated_depreciation_after',
        'net_book_value_before', 'net_book_value_after', 'calculation_details',
        'is_posted', 'posted_date', 'journal_entry_id', 'notes', 'created_by', 'posted_by'
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'posted_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation_before' => 'decimal:2',
        'accumulated_depreciation_after' => 'decimal:2',
        'net_book_value_before' => 'decimal:2',
        'net_book_value_after' => 'decimal:2',
        'is_posted' => 'boolean',
        'calculation_details' => 'array',
    ];

    // Relationships
    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scopes
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }
}




