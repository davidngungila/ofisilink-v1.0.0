<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'user_id',
        'assigned_by',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}







