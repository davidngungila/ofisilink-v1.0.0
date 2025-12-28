<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'overtime_hours',
        'overtime_amount',
        'bonus_amount',
        'allowance_amount',
        'deduction_amount',
        'nssf_amount',
        'paye_amount',
        'nhif_amount',
        'heslb_amount',
        'wcf_amount',
        'sdl_amount',
        'other_deductions',
        'employer_nssf',
        'employer_wcf',
        'employer_sdl',
        'total_employer_cost',
        'net_salary',
        'status',
    ];
    
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'allowance_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'nssf_amount' => 'decimal:2',
        'paye_amount' => 'decimal:2',
        'nhif_amount' => 'decimal:2',
        'heslb_amount' => 'decimal:2',
        'wcf_amount' => 'decimal:2',
        'sdl_amount' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'employer_nssf' => 'decimal:2',
        'employer_wcf' => 'decimal:2',
        'employer_sdl' => 'decimal:2',
        'total_employer_cost' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the payroll this item belongs to
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}