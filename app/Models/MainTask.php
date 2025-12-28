<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'timeframe',
        'team_leader_id',
        'status',
        'created_by',
        'priority',
        'category',
        'tags',
        'progress_percentage',
        'budget',
        'actual_cost',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'tags' => 'array',
    ];

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    public function getTotalActivitiesAttribute(): int
    {
        return $this->activities()->count();
    }

    public function getCompletedActivitiesAttribute(): int
    {
        return $this->activities()->where('status', 'Completed')->count();
    }

    public function getProgressPercentageAttribute(): int
    {
        // Use stored progress_percentage if available, otherwise calculate
        if (isset($this->attributes['progress_percentage']) && $this->attributes['progress_percentage'] !== null) {
            return (int)$this->attributes['progress_percentage'];
        }
        
        // Calculate based on activities
        if ($this->total_activities === 0) {
            return 0;
        }
        return round(($this->completed_activities / $this->total_activities) * 100);
    }
}
