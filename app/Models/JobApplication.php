<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'shortlisted_by',
        'shortlisted_at',
        'application_date',
    ];

    protected $casts = [
        'application_date' => 'datetime',
        'shortlisted_at' => 'datetime',
    ];

    /**
     * Get the job this application is for
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(RecruitmentJob::class, 'job_id');
    }

    /**
     * Get the user who shortlisted this application
     */
    public function shortlister(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shortlisted_by');
    }

    /**
     * Get all documents for this application
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    /**
     * Get the evaluation for this application
     */
    public function evaluation(): HasOne
    {
        return $this->hasOne(ApplicationEvaluation::class, 'application_id');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get application history
     */
    public function history(): HasMany
    {
        return $this->hasMany(ApplicationHistory::class, 'application_id');
    }

    /**
     * Get interview schedules
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class, 'application_id');
    }

    /**
     * Scope for applications by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

