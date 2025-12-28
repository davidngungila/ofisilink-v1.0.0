<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUserAssignment extends Model
{
    protected $table = 'file_user_assignments';

    protected $fillable = [
        'file_id',
        'folder_id',
        'user_id',
        'assigned_by',
        'permission_level',
        'expiry_date'
    ];

    protected $casts = [
        'file_id' => 'integer',
        'folder_id' => 'integer',
        'user_id' => 'integer',
        'assigned_by' => 'integer',
        'assigned_at' => 'datetime',
        'expiry_date' => 'date'
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function folder()
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) return false;
        return now()->isAfter($this->expiry_date);
    }
}








