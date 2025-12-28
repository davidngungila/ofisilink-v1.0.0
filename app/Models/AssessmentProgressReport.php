<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'report_date',
        'progress_text',
        'status',
        'hod_approved_at',
        'hod_approved_by',
        'hod_comments',
    ];

    protected $casts = [
        'report_date' => 'date',
        'hod_approved_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(AssessmentActivity::class, 'activity_id');
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }
}

