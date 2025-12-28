<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeNextOfKin extends Model
{
    use HasFactory;

    protected $table = 'employee_next_of_kin';

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
        'id_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}







