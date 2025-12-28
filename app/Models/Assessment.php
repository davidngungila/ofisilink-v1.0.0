<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'main_responsibility',
        'description',
        'contribution_percentage',
        'status',
        'hod_approved_at',
        'hod_approved_by',
        'hod_comments',
    ];

    protected $casts = [
        'contribution_percentage' => 'decimal:2',
        'hod_approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function activities()
    {
        return $this->hasMany(AssessmentActivity::class);
    }

    public function progressReports()
    {
        // through: assessment_activities.assessment_id -> assessment.id
        // final uses assessment_progress_reports.activity_id -> assessment_activities.id
        return $this->hasManyThrough(
            AssessmentProgressReport::class,
            AssessmentActivity::class,
            'assessment_id', // Foreign key on assessment_activities
            'activity_id',   // Foreign key on assessment_progress_reports
            'id',            // Local key on assessments
            'id'             // Local key on assessment_activities
        );
    }
}

