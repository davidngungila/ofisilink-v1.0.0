<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'category', 'is_active', 'chart_of_account_id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship to Chart of Account
     * GL Accounts are reference accounts that link to the actual Chart of Accounts
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * Check if this GL Account is synced with Chart of Accounts
     */
    public function isSynced(): bool
    {
        return $this->chart_of_account_id !== null;
    }

    /**
     * Get the account type based on category
     */
    public function getAccountType(): ?string
    {
        $categoryMap = [
            'Assets' => 'Asset',
            'Liabilities' => 'Liability',
            'Equity' => 'Equity',
            'Income' => 'Income',
            'Expense' => 'Expense',
        ];

        return $categoryMap[$this->category] ?? null;
    }

    /**
     * Get the account category enum value
     */
    public function getAccountCategory(): ?string
    {
        if (!$this->category) {
            return null;
        }

        $categoryMap = [
            'Assets' => 'Current Asset',
            'Liabilities' => 'Current Liability',
            'Equity' => 'Equity',
            'Income' => 'Operating Income',
            'Expense' => 'Operating Expense',
        ];

        return $categoryMap[$this->category] ?? null;
    }
}








