<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'asset_tag',
        'name',
        'description',
        'brand',
        'model',
        'serial_number',
        'location',
        'department_id',
        'assigned_to',
        'status',
        'condition',
        'purchase_date',
        'purchase_price',
        'current_value',
        'supplier',
        'warranty_period',
        'warranty_expiry',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2'
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issues()
    {
        return $this->hasMany(AssetIssue::class, 'asset_id');
    }

    public function activeIssues()
    {
        return $this->hasMany(AssetIssue::class, 'asset_id')
            ->whereIn('status', ['reported', 'in_progress']);
    }

    public function maintenance()
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')
            ->where('status', 'active')
            ->latest();
    }

    public function getDepreciationYearsElapsedAttribute()
    {
        if (!$this->purchase_date) return 0;
        return Carbon::now()->diffInYears($this->purchase_date);
    }

    public function calculateDepreciation()
    {
        if (!$this->purchase_date || !$this->category) {
            return $this->purchase_price;
        }

        $yearsElapsed = $this->depreciation_years_elapsed;
        $depreciationRate = $this->category->depreciation_rate / 100;
        $depreciationYears = $this->category->depreciation_years;

        if ($yearsElapsed >= $depreciationYears) {
            return 0; // Fully depreciated
        }

        // Straight-line depreciation
        $annualDepreciation = $this->purchase_price * $depreciationRate;
        $totalDepreciation = $annualDepreciation * $yearsElapsed;
        $currentValue = max(0, $this->purchase_price - $totalDepreciation);

        return round($currentValue, 2);
    }

    public function getIsWarrantyExpiredAttribute()
    {
        if (!$this->warranty_expiry) return false;
        return now()->isAfter($this->warranty_expiry);
    }

    public function getWarrantyDaysRemainingAttribute()
    {
        if (!$this->warranty_expiry) return null;
        return max(0, now()->diffInDays($this->warranty_expiry, false));
    }
}

