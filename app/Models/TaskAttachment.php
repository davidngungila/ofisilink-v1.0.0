<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_task_id',
        'activity_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function mainTask(): BelongsTo
    {
        return $this->belongsTo(MainTask::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}







