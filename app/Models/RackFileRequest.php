<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RackFileRequest extends Model
{
    protected $fillable = [
        'file_id',
        'requested_by',
        'purpose',
        'expected_return_date',
        'urgency',
        'status',
        'approved_by',
        'approved_at',
        'manager_notes'
    ];

    protected $casts = [
        'file_id' => 'integer',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
        'expected_return_date' => 'date',
        'approved_at' => 'datetime'
    ];

    public function file()
    {
        return $this->belongsTo(RackFile::class, 'file_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->expected_return_date || $this->status !== 'approved') return false;
        return Carbon::parse($this->expected_return_date)->isPast();
    }
}








