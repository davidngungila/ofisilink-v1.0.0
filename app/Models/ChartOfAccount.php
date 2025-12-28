<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'category', 'parent_id', 'description',
        'opening_balance', 'opening_balance_date', 'is_active', 'is_system',
        'sort_order', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('code');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class, 'account_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship to GL Account (if this Chart of Account is linked from a GL Account)
     * Note: Multiple GL Accounts can link to the same Chart of Account
     */
    public function glAccounts(): HasMany
    {
        return $this->hasMany(GlAccount::class, 'chart_of_account_id');
    }

    /**
     * Relationship to Cash Box (if this Chart of Account is linked from a Cash Box)
     * Note: Multiple Cash Boxes can link to the same Chart of Account
     */
    public function cashBoxes(): HasMany
    {
        return $this->hasMany(CashBox::class, 'chart_of_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function getCurrentBalanceAttribute()
    {
        $balance = $this->ledgerEntries()
            ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;
        
        return $this->opening_balance + $balance;
    }
    
    public function getCurrentBalance()
    {
        return $this->current_balance;
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_system && 
               $this->ledgerEntries()->count() === 0 &&
               $this->children()->count() === 0;
    }
}

