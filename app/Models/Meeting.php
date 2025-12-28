<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'reference_code',
        'category_id',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'meeting_mode',
        'virtual_link',
        'status',
        'approval_target',
        'approval_notes',
        'agenda_overview',
        'previous_actions_included',
        'minutes_status',
        'created_by',
        'updated_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'previous_actions_included' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MeetingCategory::class, 'category_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(MeetingAgenda::class);
    }

    public function minutes(): HasOne
    {
        return $this->hasOne(MeetingMinute::class);
    }

    public function minuteItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            MeetingMinuteItem::class,
            MeetingMinute::class,
            'meeting_id',          // Foreign key on meeting_minutes
            'meeting_minute_id',   // Foreign key on meeting_minute_items
            'id',                  // Local key on meetings
            'id'                   // Local key on meeting_minutes
        );
    }
}

