<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RackFile extends Model
{
    protected $fillable = [
        'folder_id',
        'file_name',
        'file_number',
        'description',
        'file_type',
        'confidential_level',
        'tags',
        'file_date',
        'retention_period',
        'notes',
        'created_by',
        'status',
        'current_holder',
        'last_returned'
    ];

    protected $casts = [
        'folder_id' => 'integer',
        'created_by' => 'integer',
        'current_holder' => 'integer',
        'file_date' => 'date',
        'retention_period' => 'integer',
        'last_returned' => 'datetime'
    ];

    public function folder()
    {
        return $this->belongsTo(RackFolder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function holder()
    {
        return $this->belongsTo(User::class, 'current_holder');
    }

    public function requests()
    {
        return $this->hasMany(RackFileRequest::class, 'file_id');
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'issued') return false;
        
        return $this->requests()
            ->where('status', 'approved')
            ->where('expected_return_date', '<', Carbon::now())
            ->exists();
    }
}








