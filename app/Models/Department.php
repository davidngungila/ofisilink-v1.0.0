<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'head_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_departments')
                    ->withPivot(['is_primary', 'is_active', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Get primary users in this department
     */
    public function primaryUsers()
    {
        return $this->hasMany(User::class, 'primary_department_id');
    }

    /**
     * Get file folders in this department
     */
    public function fileFolders()
    {
        return $this->hasMany(FileFolder::class, 'department_id');
    }

    /**
     * Get rack folders in this department
     */
    public function rackFolders()
    {
        return $this->hasMany(RackFolder::class, 'department_id');
    }

    /**
     * Get leave requests from users in this department
     */
    public function leaveRequests()
    {
        return $this->hasManyThrough(LeaveRequest::class, User::class, 'primary_department_id', 'employee_id');
    }

    /**
     * Get petty cash vouchers from users in this department
     */
    public function pettyCashVouchers()
    {
        return $this->hasManyThrough(PettyCashVoucher::class, User::class, 'primary_department_id', 'user_id');
    }

    /**
     * Get payroll records for users in this department
     */
    public function payrolls()
    {
        return $this->hasManyThrough(Payroll::class, User::class, 'primary_department_id', 'user_id');
    }

    /**
     * Get attendance policies for this department
     */
    public function attendancePolicies()
    {
        return $this->hasMany(AttendancePolicy::class, 'department_id');
    }

    /**
     * Scope for active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get department statistics
     */
    public function getStatsAttribute()
    {
        return [
            'users_count' => $this->primaryUsers()->active()->count(),
            'file_folders_count' => $this->fileFolders()->count(),
            'rack_folders_count' => $this->rackFolders()->count(),
            'leave_requests_count' => $this->leaveRequests()->count(),
        ];
    }
}
