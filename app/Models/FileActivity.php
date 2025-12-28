<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileActivity extends Model
{
    protected $table = 'file_activities';

    protected $fillable = [
        'file_id',
        'user_id',
        'activity_type',
        'activity_date',
        'details'
    ];

    protected $casts = [
        'file_id' => 'integer',
        'user_id' => 'integer',
        'activity_date' => 'datetime',
        'details' => 'array'
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}








