<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileAccessRequest extends Model
{
    protected $table = 'file_access_requests';

    protected $fillable = [
        'file_id',
        'user_id',
        'purpose',
        'urgency',
        'required_until',
        'status',
        'processed_by',
        'processed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'file_id' => 'integer',
        'user_id' => 'integer',
        'processed_by' => 'integer',
        'required_until' => 'date',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime'
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}








