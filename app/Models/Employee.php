<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_number',
        'position',
        'department_id',
        'hire_date',
        'salary',
        'employment_type',
        'employment_status',
        'manager_id',
        'emergency_contact',
        'emergency_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_address',
        'bank_name',
        'bank_account_number',
        'tin_number',
        'nssf_number',
        'nhif_number',
        'heslb_number',
        'has_student_loan',
        'termination_date',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'emergency_contact' => 'array',
        'has_student_loan' => 'boolean',
    ];

    /**
     * Get the user associated with this employee
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get leave requests for this employee
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'user_id');
    }

    /**
     * Get payroll records for this employee
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'user_id', 'user_id');
    }

    /**
     * Get attendance records for this employee
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}