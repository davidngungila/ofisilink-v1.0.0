<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'prefix',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function folders()
    {
        return $this->hasMany(RackFolder::class, 'category_id');
    }
}








