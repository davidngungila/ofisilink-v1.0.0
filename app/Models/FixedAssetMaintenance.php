<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetMaintenance extends Model
{
    protected $fillable = [
        'fixed_asset_id', 'maintenance_date', 'maintenance_type', 'service_provider',
        'description', 'cost', 'invoice_number', 'next_maintenance_date', 'status',
        'notes', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
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
    public function scopeScheduled($query)
    {
        return $query->where('status', 'Scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('next_maintenance_date', '>=', now())
                    ->where('status', '!=', 'Cancelled');
    }
}




