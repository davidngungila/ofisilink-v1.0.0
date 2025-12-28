<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'title',
        'description',
        'scheduled_date',
        'completed_date',
        'status',
        'assigned_to',
        'vendor_id',
        'vendor_name',
        'cost',
        'notes'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->scheduled_date || $this->status === 'completed') {
            return false;
        }
        return now()->isAfter($this->scheduled_date);
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }
}

