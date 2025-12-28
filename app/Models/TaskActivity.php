<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_task_id',
        'name',
        'start_date',
        'end_date',
        'actual_end_date',
        'timeframe',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'depends_on_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function mainTask(): BelongsTo
    {
        return $this->belongsTo(MainTask::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ActivityAssignment::class, 'activity_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ActivityReport::class, 'activity_id')->latest('report_date');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'activity_assignments', 'activity_id', 'user_id')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class, 'depends_on_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(TaskActivity::class, 'depends_on_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'activity_id')->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'activity_id')->latest();
    }
}
