<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class FixedAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'asset_code', 'barcode_number', 'name', 'description', 'serial_number', 'manufacturer', 'model',
        'location', 'department_id', 'assigned_to', 'purchase_date', 'purchase_cost', 'additional_costs',
        'total_cost', 'vendor_id', 'invoice_number', 'purchase_order_number', 'depreciation_method',
        'depreciation_rate', 'useful_life_years', 'useful_life_units', 'salvage_value',
        'depreciation_start_date', 'depreciation_end_date', 'accumulated_depreciation', 'net_book_value',
        'current_market_value', 'asset_account_id', 'depreciation_expense_account_id',
        'accumulated_depreciation_account_id', 'status', 'disposal_date', 'disposal_proceeds',
        'disposal_notes', 'warranty_period', 'warranty_expiry', 'notes', 'custom_fields',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'depreciation_start_date' => 'date',
        'depreciation_end_date' => 'date',
        'disposal_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'additional_costs' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_book_value' => 'decimal:2',
        'current_market_value' => 'decimal:2',
        'disposal_proceeds' => 'decimal:2',
        'useful_life_years' => 'integer',
        'useful_life_units' => 'integer',
        'custom_fields' => 'array',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
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

    public function depreciations(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciation::class, 'fixed_asset_id')->orderBy('depreciation_date');
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(FixedAssetDisposal::class, 'fixed_asset_id');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(FixedAssetMaintenance::class, 'fixed_asset_id')->orderBy('maintenance_date', 'desc');
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
        return $query->where('status', 'Active');
    }

    public function scopeDepreciated($query)
    {
        return $query->where('status', 'Depreciated');
    }

    public function scopeDisposed($query)
    {
        return $query->where('status', 'Disposed');
    }

    // Helper Methods
    public function calculateDepreciation($asOfDate = null)
    {
        $asOfDate = $asOfDate ? Carbon::parse($asOfDate) : Carbon::now();
        
        if ($asOfDate->lt($this->depreciation_start_date)) {
            return [
                'depreciation_amount' => 0,
                'accumulated_depreciation' => 0,
                'net_book_value' => $this->total_cost
            ];
        }

        $depreciableAmount = $this->total_cost - $this->salvage_value;
        
        switch ($this->depreciation_method) {
            case 'Straight Line':
                return $this->calculateStraightLineDepreciation($depreciableAmount, $asOfDate);
            
            case 'Declining Balance':
                return $this->calculateDecliningBalanceDepreciation($depreciableAmount, $asOfDate);
            
            case 'Sum of Years Digits':
                return $this->calculateSumOfYearsDigitsDepreciation($depreciableAmount, $asOfDate);
            
            case 'Units of Production':
                return $this->calculateUnitsOfProductionDepreciation($depreciableAmount, $asOfDate);
            
            default:
                return $this->calculateStraightLineDepreciation($depreciableAmount, $asOfDate);
        }
    }

    private function calculateStraightLineDepreciation($depreciableAmount, $asOfDate)
    {
        $monthsElapsed = $this->depreciation_start_date->diffInMonths($asOfDate);
        $totalMonths = $this->useful_life_years * 12;
        
        if ($monthsElapsed >= $totalMonths) {
            $accumulatedDepreciation = $depreciableAmount;
        } else {
            $monthlyDepreciation = $depreciableAmount / $totalMonths;
            $accumulatedDepreciation = $monthlyDepreciation * $monthsElapsed;
        }
        
        $netBookValue = $this->total_cost - $accumulatedDepreciation;
        
        return [
            'depreciation_amount' => $depreciableAmount / $totalMonths, // Monthly amount
            'accumulated_depreciation' => min($accumulatedDepreciation, $depreciableAmount),
            'net_book_value' => max($netBookValue, $this->salvage_value)
        ];
    }

    private function calculateDecliningBalanceDepreciation($depreciableAmount, $asOfDate)
    {
        $monthsElapsed = $this->depreciation_start_date->diffInMonths($asOfDate);
        $rate = ($this->depreciation_rate / 100) / 12; // Monthly rate
        $accumulatedDepreciation = 0;
        $bookValue = $this->total_cost;
        
        for ($i = 0; $i < $monthsElapsed && $i < ($this->useful_life_years * 12); $i++) {
            $monthlyDepreciation = $bookValue * $rate;
            $accumulatedDepreciation += $monthlyDepreciation;
            $bookValue -= $monthlyDepreciation;
            
            // Ensure we don't depreciate below salvage value
            if ($bookValue <= $this->salvage_value) {
                $accumulatedDepreciation = $this->total_cost - $this->salvage_value;
                break;
            }
        }
        
        $netBookValue = $this->total_cost - $accumulatedDepreciation;
        
        return [
            'depreciation_amount' => $bookValue * $rate, // Current month amount
            'accumulated_depreciation' => min($accumulatedDepreciation, $depreciableAmount),
            'net_book_value' => max($netBookValue, $this->salvage_value)
        ];
    }

    private function calculateSumOfYearsDigitsDepreciation($depreciableAmount, $asOfDate)
    {
        $yearsElapsed = $this->depreciation_start_date->diffInYears($asOfDate);
        $totalYears = $this->useful_life_years;
        $sumOfYears = ($totalYears * ($totalYears + 1)) / 2;
        
        $accumulatedDepreciation = 0;
        for ($year = 1; $year <= min($yearsElapsed, $totalYears); $year++) {
            $yearFraction = ($totalYears - $year + 1) / $sumOfYears;
            $accumulatedDepreciation += $depreciableAmount * $yearFraction;
        }
        
        $netBookValue = $this->total_cost - $accumulatedDepreciation;
        
        return [
            'depreciation_amount' => $depreciableAmount / $totalYears, // Approximate annual
            'accumulated_depreciation' => min($accumulatedDepreciation, $depreciableAmount),
            'net_book_value' => max($netBookValue, $this->salvage_value)
        ];
    }

    private function calculateUnitsOfProductionDepreciation($depreciableAmount, $asOfDate)
    {
        // This requires tracking actual usage/units
        // For now, return straight line as fallback
        return $this->calculateStraightLineDepreciation($depreciableAmount, $asOfDate);
    }

    public function updateNetBookValue()
    {
        $this->net_book_value = $this->total_cost - $this->accumulated_depreciation;
        $this->save();
    }

    /**
     * Generate unique barcode number
     */
    public static function generateBarcodeNumber($categoryCode = null)
    {
        $prefix = $categoryCode ? strtoupper(substr($categoryCode, 0, 3)) : 'AST';
        $year = date('y');
        $month = date('m');
        
        // Get the last barcode number for this prefix/year/month
        $lastAsset = self::where('barcode_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('barcode_number', 'desc')
            ->first();
        
        if ($lastAsset && $lastAsset->barcode_number) {
            $lastNumber = (int) substr($lastAsset->barcode_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate barcode number if not exists
     */
    public function generateBarcodeIfNotExists()
    {
        if (!$this->barcode_number) {
            $categoryCode = $this->category ? $this->category->code : null;
            $this->barcode_number = self::generateBarcodeNumber($categoryCode);
            $this->save();
        }
        return $this->barcode_number;
    }
}


