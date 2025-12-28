<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'currency', 'current_balance', 'is_active', 'chart_of_account_id'
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship to Chart of Account
     * Cash Boxes represent physical cash containers and link to Asset accounts in Chart of Accounts
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * Check if this Cash Box is synced with Chart of Accounts
     */
    public function isSynced(): bool
    {
        return $this->chart_of_account_id !== null;
    }
}








