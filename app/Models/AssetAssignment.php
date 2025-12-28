<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'assigned_to',
        'assigned_by',
        'assigned_date',
        'return_date',
        'status',
        'notes',
        'returned_to'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'return_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function returnedTo()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getDaysAssignedAttribute()
    {
        if ($this->return_date) {
            return $this->assigned_date->diffInDays($this->return_date);
        }
        return $this->assigned_date->diffInDays(now());
    }
}

