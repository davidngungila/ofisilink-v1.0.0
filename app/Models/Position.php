<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'department_id',
        'min_salary',
        'max_salary',
        'employment_type',
        'requirements',
        'responsibilities',
        'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department that owns this position
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get employees with this position
     * Note: Since employees.position is stored as string (not foreign key),
     * we use a custom query method instead of a relationship
     */
    public function getEmployees()
    {
        return Employee::where('position', $this->title)
            ->with('user')
            ->get();
    }
    
    /**
     * Get employees count with this position (helper method)
     */
    public function getEmployeesCountAttribute()
    {
        return Employee::where('position', $this->title)->count();
    }

    /**
     * Scope for active positions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for positions in a specific department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
