<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAssetCategory extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'depreciation_method', 'default_depreciation_rate',
        'default_useful_life_years', 'asset_account_id', 'depreciation_expense_account_id',
        'accumulated_depreciation_account_id', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'default_depreciation_rate' => 'decimal:2',
        'default_useful_life_years' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function assets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'asset_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'depreciation_expense_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}




