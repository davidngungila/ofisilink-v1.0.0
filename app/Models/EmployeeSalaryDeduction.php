<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSalaryDeduction extends Model
{
    use HasFactory;

    protected $table = 'employee_salary_deductions';

    protected $fillable = [
        'employee_id',
        'deduction_type',
        'description',
        'amount',
        'frequency',
        'start_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee this deduction belongs to
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}



