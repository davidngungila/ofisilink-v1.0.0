<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeEducation extends Model
{
    use HasFactory;

    protected $table = 'employee_educations';

    protected $fillable = [
        'user_id',
        'institution_name',
        'qualification',
        'field_of_study',
        'start_year',
        'end_year',
        'grade',
        'description',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}







