<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileFolder extends Model
{
    protected $fillable = [
        'name',
        'folder_code',
        'description',
        'parent_id',
        'path',
        'created_by',
        'access_level',
        'department_id'
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'created_by' => 'integer',
        'department_id' => 'integer'
    ];

    public function parent()
    {
        return $this->belongsTo(FileFolder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(FileFolder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignments()
    {
        return $this->hasMany(FileUserAssignment::class, 'folder_id');
    }

    public function getAccessibleFileCountAttribute()
    {
        return $this->files()
            ->where(function($query) {
                $query->where('access_level', 'public')
                    ->orWhere(function($q) {
                        // Add department check if needed
                    });
            })
            ->count();
    }

    /**
     * Scope for root folders
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for folders accessible by user
     */
    public function scopeAccessibleBy($query, $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('access_level', 'public')
              ->orWhere(function($q2) use ($user) {
                  $q2->where('access_level', 'department')
                     ->where('department_id', $user->primary_department_id);
              })
              ->orWhere(function($q3) use ($user) {
                  $q3->where('access_level', 'private')
                     ->whereHas('files.userAssignments', function($q4) use ($user) {
                         $q4->where('user_id', $user->id);
                     });
              });
        });
    }

    /**
     * Get folder path as breadcrumb
     */
    public function getBreadcrumbAttribute()
    {
        $breadcrumb = [];
        $folder = $this;
        
        while ($folder) {
            array_unshift($breadcrumb, $folder);
            $folder = $folder->parent;
        }
        
        return $breadcrumb;
    }

    /**
     * Get total files count including subfolders
     */
    public function getTotalFilesCountAttribute()
    {
        $count = $this->files()->count();
        
        foreach ($this->subfolders as $subfolder) {
            $count += $subfolder->total_files_count;
        }
        
        return $count;
    }
}

