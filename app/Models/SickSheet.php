<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SickSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'sheet_number',
        'employee_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'medical_document_path',
        'status',
        'hr_reviewed_at',
        'hr_reviewed_by',
        'hr_comments',
        'hod_approved_at',
        'hod_approved_by',
        'hod_comments',
        'return_submitted_at',
        'return_remarks',
        'hr_final_verified_at',
        'hr_final_verified_by',
        'hr_final_comments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'hr_reviewed_at' => 'datetime',
        'hod_approved_at' => 'datetime',
        'return_submitted_at' => 'datetime',
        'hr_final_verified_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function hrFinalVerifier()
    {
        return $this->belongsTo(User::class, 'hr_final_verified_by');
    }
}







