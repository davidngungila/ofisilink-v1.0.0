<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'user_id',
        'report_date',
        'work_description',
        'next_activities',
        'attachment_path',
        'completion_status',
        'reason_if_delayed',
        'status',
        'approved_by',
        'approved_at',
        'approver_comments',
    ];

    protected $casts = [
        'report_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class, 'activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
