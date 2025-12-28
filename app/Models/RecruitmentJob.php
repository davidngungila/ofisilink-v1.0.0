<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentJob extends Model
{
    protected $table = 'recruitment_jobs';

    protected $fillable = [
        'job_title',
        'job_description',
        'qualifications',
        'application_deadline',
        'required_attachments',
        'interview_mode',
        'status',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'approved_at' => 'datetime',
        'required_attachments' => 'array',
        'interview_mode' => 'array',
    ];

    /**
     * Get the user who created this job
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this job
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    /**
     * Scope for active jobs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope for pending approval jobs
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'Pending Approval');
    }

    /**
     * Check if job deadline has passed
     */
    public function isDeadlinePassed(): bool
    {
        return $this->application_deadline < now()->startOfDay();
    }
}

