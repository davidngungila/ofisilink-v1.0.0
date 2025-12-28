<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEvaluation extends Model
{
    protected $fillable = [
        'application_id',
        'interviewer_id',
        'written_score',
        'practical_score',
        'oral_score',
        'comments',
    ];

    protected $casts = [
        'written_score' => 'decimal:2',
        'practical_score' => 'decimal:2',
        'oral_score' => 'decimal:2',
    ];

    /**
     * Get the application this evaluation belongs to
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Get the interviewer who did this evaluation
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * Calculate total score
     */
    public function getTotalScoreAttribute(): ?float
    {
        $scores = array_filter([
            $this->written_score,
            $this->practical_score,
            $this->oral_score,
        ]);

        if (empty($scores)) {
            return null;
        }

        return round(array_sum($scores) / count($scores), 2);
    }
}

