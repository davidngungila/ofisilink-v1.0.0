<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'participant_type',
        'user_id',
        'name',
        'phone',
        'email',
        'institution',
        'role',
        'is_required',
        'attendance_status',
        'invitation_sent_at',
        'checked_in_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'invitation_sent_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

