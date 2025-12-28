<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetDisposal extends Model
{
    protected $fillable = [
        'fixed_asset_id', 'disposal_date', 'disposal_method', 'disposal_proceeds',
        'net_book_value_at_disposal', 'gain_loss', 'disposal_reference',
        'disposal_reason', 'notes', 'is_posted', 'posted_date', 'journal_entry_id',
        'created_by', 'posted_by'
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'posted_date' => 'date',
        'disposal_proceeds' => 'decimal:2',
        'net_book_value_at_disposal' => 'decimal:2',
        'gain_loss' => 'decimal:2',
        'is_posted' => 'boolean',
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
}




