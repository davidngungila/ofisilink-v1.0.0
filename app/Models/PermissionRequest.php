<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'name',
        'time_mode',
        'start_datetime',
        'end_datetime',
        'reason_type',
        'reason_description',
        'status',
        'hr_initial_reviewed',
        'hr_initial_reviewed_by',
        'hr_initial_comments',
        'hod_reviewed',
        'hod_reviewed_by',
        'hod_comments',
        'hr_final_reviewed',
        'hr_final_reviewed_by',
        'hr_final_comments',
        'return_datetime',
        'return_remarks',
        'return_submitted_at',
        'hod_return_reviewed',
        'hod_return_comments',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'hr_initial_reviewed' => 'datetime',
        'hod_reviewed' => 'datetime',
        'hr_final_reviewed' => 'datetime',
        'return_submitted_at' => 'datetime',
        'hod_return_reviewed' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hrInitialReviewer()
    {
        return $this->belongsTo(User::class, 'hr_initial_reviewed_by');
    }

    public function hodReviewer()
    {
        return $this->belongsTo(User::class, 'hod_reviewed_by');
    }

    public function hrFinalReviewer()
    {
        return $this->belongsTo(User::class, 'hr_final_reviewed_by');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending_hr' => ['class' => 'warning', 'text' => 'Pending HR Review'],
            'pending_hod' => ['class' => 'info', 'text' => 'Pending HOD Approval'],
            'pending_hr_final' => ['class' => 'primary', 'text' => 'Pending HR Final Approval'],
            'approved' => ['class' => 'success', 'text' => 'Approved'],
            'rejected' => ['class' => 'danger', 'text' => 'Rejected'],
            'in_progress' => ['class' => 'info', 'text' => 'In Progress'],
            'return_pending' => ['class' => 'warning', 'text' => 'Return Pending HR Verification'],
            'return_rejected' => ['class' => 'danger', 'text' => 'Return Rejected'],
            'completed' => ['class' => 'secondary', 'text' => 'Completed'],
        ];

        return $badges[$this->status] ?? ['class' => 'secondary', 'text' => ucwords(str_replace('_', ' ', $this->status))];
    }
}
