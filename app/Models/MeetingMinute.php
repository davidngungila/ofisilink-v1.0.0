<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'prepared_by',
        'status',
        'summary',
        'next_meeting_date',
        'approved_by',
        'approved_at',
        'published_at',
    ];

    protected $casts = [
        'next_meeting_date' => 'date',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MeetingMinuteItem::class, 'meeting_minute_id');
    }
}

