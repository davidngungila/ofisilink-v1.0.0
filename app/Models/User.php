<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'employee_id',
        'enroll_id',
        'registered_on_device',
        'device_registered_at',
        'phone',
        'mobile',
        'marital_status',
        'date_of_birth',
        'gender',
        'nationality',
        'address',
        'hire_date',
        'primary_department_id',
        'is_active',
        'blocked_at',
        'blocked_until',
        'block_reason',
        'blocked_by',
        'failed_login_attempts',
        'last_failed_login_at',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'blocked_at' => 'datetime',
            'blocked_until' => 'datetime',
            'registered_on_device' => 'boolean',
            'device_registered_at' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }
    
    /**
     * Check if user is currently blocked
     */
    public function getIsBlockedAttribute(): bool
    {
        if (!$this->blocked_at) {
            return false;
        }
        
        // If blocked_until is null, user is blocked forever
        if (!$this->blocked_until) {
            return true;
        }
        
        // Check if block period has expired
        return now()->isBefore($this->blocked_until);
    }
    
    /**
     * Get the user who blocked this user
     */
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get the user's roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the user's primary department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'primary_department_id');
    }
    
    /**
     * Get the user's departments
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments')
                    ->select('departments.id', 'departments.name', 'departments.code', 'departments.is_active')
                    ->withPivot(['is_primary', 'is_active', 'joined_at'])
                    ->withTimestamps();
    }
    
    /**
     * Get salary deductions for the employee
     */
    public function salaryDeductions()
    {
        return $this->hasMany(EmployeeSalaryDeduction::class, 'employee_id');
    }

    /**
     * Get the user's primary department
     */
    public function primaryDepartment()
    {
        return $this->belongsTo(Department::class, 'primary_department_id');
    }

    /**
     * Get all permissions for the user (custom method - use getAllPermissions() from Spatie instead)
     * @deprecated Use getAllPermissions() from Spatie HasRoles trait instead
     */
    public function customPermissions()
    {
        return $this->roles()->with('permissions')->get()
                    ->pluck('permissions')->flatten()->unique('id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        return $this->roles->contains($role);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Check if user has a specific permission
     * Note: Spatie's HasRoles trait also provides hasPermission() method
     */
    public function hasPermission($permission)
    {
        // Use Spatie's getAllPermissions() method
        return $this->getAllPermissions()->contains('name', $permission);
    }

    /**
     * Check if user is system admin
     */
    public function isSystemAdmin()
    {
        return $this->hasRole('System Admin');
    }

    /**
     * Get the user's employee record
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the user's bank accounts
     */
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get the user's primary bank account
     */
    public function primaryBankAccount()
    {
        return $this->hasOne(BankAccount::class)->where('is_primary', true);
    }

    /**
     * Get the user's education records
     */
    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class)->orderBy('order');
    }

    /**
     * Get the user's family members
     */
    public function family()
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    /**
     * Get the user's next of kin
     */
    public function nextOfKin()
    {
        return $this->hasMany(EmployeeNextOfKin::class);
    }

    /**
     * Get the user's referees
     */
    public function referees()
    {
        return $this->hasMany(EmployeeReferee::class)->orderBy('order');
    }

    /**
     * Get the user's employee documents
     */
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'user_id')->where('is_active', true)->orderBy('created_at', 'desc');
    }

    /**
     * Alias for documents relationship (for backward compatibility)
     */
    public function employeeDocuments()
    {
        return $this->documents();
    }

    /**
     * Get the user's payroll items
     */
    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class, 'employee_id');
    }

    /**
     * Get the user's leave requests
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    /**
     * Get leave requests reviewed by this user
     */
    public function reviewedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'reviewed_by');
    }

    /**
     * Get leave requests processed by this user
     */
    public function processedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'documents_processed_by');
    }

    /**
     * Get the user's leave balances
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    /**
     * Get the user's leave recommendations
     */
    public function leaveRecommendations()
    {
        return $this->hasMany(LeaveRecommendation::class, 'employee_id');
    }

    /**
     * Get the user's attendance records
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    /**
     * Get leave documents generated by this user
     */
    public function generatedLeaveDocuments()
    {
        return $this->hasMany(LeaveDocument::class, 'generated_by');
    }

    /**
     * Get the user's file access requests
     */
    public function fileAccessRequests()
    {
        return $this->hasMany(FileAccessRequest::class, 'user_id');
    }

    /**
     * Get files assigned to this user
     */
    public function assignedFiles()
    {
        return $this->belongsToMany(FileModel::class, 'file_user_assignments')
                    ->withPivot(['permission_level', 'assigned_at', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get the user's petty cash vouchers
     */
    public function pettyCashVouchers()
    {
        return $this->hasMany(PettyCashVoucher::class, 'user_id');
    }

    /**
     * Get the user's payroll records
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'user_id');
    }

    /**
     * Get files uploaded by this user
     */
    public function uploadedFiles()
    {
        return $this->hasMany(FileModel::class, 'uploaded_by');
    }

    /**
     * Get folders created by this user
     */
    public function createdFolders()
    {
        return $this->hasMany(FileFolder::class, 'created_by');
    }

    /**
     * Get rack files requested by this user
     */
    public function rackFileRequests()
    {
        return $this->hasMany(RackFileRequest::class, 'user_id');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    /**
     * Get device tokens for push notifications
     */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Get active device tokens
     */
    public function activeDeviceTokens()
    {
        return $this->hasMany(DeviceToken::class)->where('is_active', true);
    }

    /**
     * Get the user's department ID (alias for primary_department_id)
     */
    public function getDepartmentIdAttribute()
    {
        return $this->primary_department_id;
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users in a specific department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('primary_department_id', $departmentId);
    }

    /**
     * Format phone number to standard format: 255XXXXXXXXX (12 digits)
     * 
     * @param string|null $value
     * @return void
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $this->formatPhoneNumber($value);
    }

    /**
     * Format mobile number to standard format: 255XXXXXXXXX (12 digits)
     * 
     * @param string|null $value
     * @return void
     */
    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = $this->formatPhoneNumber($value);
    }

    /**
     * Format phone number to standard Tanzania format
     * Format: 255XXXXXXXXX (country code 255 + 9 digits = 12 total)
     * 
     * @param string|null $phoneNumber
     * @return string|null
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (empty($cleaned)) {
            return null;
        }

        // Remove leading 0 if present
        $cleaned = ltrim($cleaned, '0');

        // Add country code if not present
        if (!str_starts_with($cleaned, '255')) {
            $cleaned = '255' . $cleaned;
        }

        // Validate format: should be 255 followed by 9 digits (12 total)
        if (preg_match('/^255[0-9]{9}$/', $cleaned)) {
            return $cleaned;
        }

        // If format is incorrect but has valid length, try to fix
        if (strlen($cleaned) == 12 && str_starts_with($cleaned, '255')) {
            return $cleaned;
        }

        // Return as-is if can't format properly (will fail validation elsewhere)
        return $cleaned;
    }
}
