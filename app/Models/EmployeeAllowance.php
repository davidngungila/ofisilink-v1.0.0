<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'amount',
        'allowance_type',
        'description',
        'notes',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
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
}
