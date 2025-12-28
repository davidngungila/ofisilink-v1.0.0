<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeReferee extends Model
{
    use HasFactory;

    protected $table = 'employee_referees';

    protected $fillable = [
        'user_id',
        'name',
        'position',
        'organization',
        'phone',
        'email',
        'address',
        'relationship',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}







