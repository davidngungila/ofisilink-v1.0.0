<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveDependent extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'name',
        'relationship',
        'certificate_path',
        'fare_amount',
    ];

    protected $casts = [
        'fare_amount' => 'decimal:2',
    ];

    /**
     * Get the leave request
     */
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }
}