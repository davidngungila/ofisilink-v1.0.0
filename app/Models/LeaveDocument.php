<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'document_type',
        'file_path',
        'file_name',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Get the leave request
     */
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    /**
     * Get the user who generated the document
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}