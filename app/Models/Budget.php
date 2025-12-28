<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'budget_name', 'budget_type', 'fiscal_year', 'start_date', 'end_date',
        'department_id', 'status', 'notes', 'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class, 'budget_id');
    }

    // Helper methods
    public function getTotalBudgetedAttribute()
    {
        return $this->items()->sum('budgeted_amount');
    }

    public function getTotalActualAttribute()
    {
        return $this->items()->sum('actual_amount');
    }

    public function getTotalVarianceAttribute()
    {
        return $this->items()->sum('variance');
    }
}



