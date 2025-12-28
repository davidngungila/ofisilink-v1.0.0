<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackFolder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'rack_number',
        'rack_range_start',
        'rack_range_end',
        'category_id',
        'department_id',
        'access_level',
        'location',
        'notes',
        'created_by',
        'status'
    ];

    protected $casts = [
        'rack_range_start' => 'integer',
        'rack_range_end' => 'integer',
        'category_id' => 'integer',
        'department_id' => 'integer',
        'created_by' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(RackCategory::class, 'category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(RackFile::class, 'folder_id');
    }

    public function activities()
    {
        return $this->hasMany(RackActivity::class, 'folder_id');
    }

    public function getFileCountAttribute()
    {
        return $this->files()->where('status', '!=', 'archived')->count();
    }

    public function getIssuedCountAttribute()
    {
        return $this->files()->where('status', 'issued')->count();
    }
}

