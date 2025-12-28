<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackActivity extends Model
{
    protected $fillable = [
        'folder_id',
        'user_id',
        'activity_type',
        'activity_date',
        'details'
    ];

    protected $casts = [
        'folder_id' => 'integer',
        'user_id' => 'integer',
        'activity_date' => 'datetime',
        'details' => 'array'
    ];

    public function folder()
    {
        return $this->belongsTo(RackFolder::class, 'folder_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get file from details if available
     */
    public function getFileAttribute()
    {
        if ($this->details && isset($this->details['file_id'])) {
            return RackFile::find($this->details['file_id']);
        }
        return null;
    }
}








