<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetIssue extends Model
{
    protected $fillable = [
        'asset_id',
        'reported_by',
        'assigned_to',
        'priority',
        'status',
        'issue_type',
        'title',
        'description',
        'resolution_notes',
        'reported_date',
        'resolved_date',
        'cost'
    ];

    protected $casts = [
        'reported_date' => 'date',
        'resolved_date' => 'date',
        'cost' => 'decimal:2'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getIsResolvedAttribute()
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function getDaysOpenAttribute()
    {
        if ($this->resolved_date) {
            return $this->reported_date->diffInDays($this->resolved_date);
        }
        return $this->reported_date->diffInDays(now());
    }
}

