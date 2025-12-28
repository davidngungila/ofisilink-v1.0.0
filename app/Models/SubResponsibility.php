<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubResponsibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_responsibility_id',
        'title',
        'description',
    ];

    public function main(): BelongsTo
    {
        return $this->belongsTo(MainResponsibility::class, 'main_responsibility_id');
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class, 'sub_responsibility_id')->latest('period_end');
    }
}








