<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeFamily extends Model
{
    use HasFactory;

    protected $table = 'employee_family';

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'date_of_birth',
        'gender',
        'occupation',
        'phone',
        'email',
        'address',
        'is_dependent',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_dependent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}







