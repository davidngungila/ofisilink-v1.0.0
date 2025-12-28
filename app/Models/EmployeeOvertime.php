<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeOvertime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'hours',
        'hourly_rate',
        'amount',
        'description',
        'notes',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the user who created this record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this record
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate amount based on hours and hourly rate
     */
    public function calculateAmount()
    {
        $this->amount = $this->hours * $this->hourly_rate * 1.5; // 1.5x for overtime
        return $this->amount;
    }
}
