<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    protected $fillable = [
        'application_id',
        'interview_type',
        'scheduled_at',
        'location',
        'notes',
        'scheduled_by',
        'interviewer_id',
        'status',
        'completed_at',
        'feedback',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the application this interview is for
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Get the user who scheduled this interview
     */
    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /**
     * Get the interviewer
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * Scope for scheduled interviews
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'Scheduled');
    }

    /**
     * Scope for upcoming interviews
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'Scheduled')
            ->where('scheduled_at', '>=', now());
    }
}

