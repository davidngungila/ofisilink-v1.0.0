<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingMinuteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_minute_id',
        'agenda_id',
        'responsible_id',
        'source_meeting_id',
        'title',
        'notes',
        'decisions',
        'action_required',
        'due_date',
        'status',
        'from_previous',
        'completed_at',
    ];

    protected $casts = [
        'action_required' => 'boolean',
        'from_previous' => 'boolean',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function minute(): BelongsTo
    {
        return $this->belongsTo(MeetingMinute::class, 'meeting_minute_id');
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(MeetingAgenda::class, 'agenda_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function sourceMeeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'source_meeting_id');
    }
}

