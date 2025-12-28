<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(MainTask::class, 'category', 'name');
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(MainTask::class, 'category', 'name')
            ->whereIn('status', ['planning', 'in_progress']);
    }
}
