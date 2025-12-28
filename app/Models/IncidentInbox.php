<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentInbox extends Model
{
    use HasFactory;

    protected $table = 'incident_inbox';

    protected $fillable = [
        'message_id', 'from_name', 'from_email', 'subject', 'body', 'received_at', 'status'
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];
}








