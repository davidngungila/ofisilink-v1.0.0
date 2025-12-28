<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'depreciation_years',
        'depreciation_rate',
        'is_active'
    ];

    protected $casts = [
        'depreciation_years' => 'integer',
        'depreciation_rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function activeAssets()
    {
        return $this->hasMany(Asset::class, 'category_id')
            ->whereIn('status', ['available', 'assigned']);
    }
}

