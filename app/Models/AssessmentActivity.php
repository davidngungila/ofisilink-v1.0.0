<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'activity_name',
        'description',
        'reporting_frequency',
        'contribution_percentage',
    ];

    protected $casts = [
        'contribution_percentage' => 'decimal:2',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function progressReports()
    {
        return $this->hasMany(AssessmentProgressReport::class, 'activity_id');
    }
}

