<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'folder_id',
        'original_name',
        'stored_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
        'uploaded_by',
        'access_level',
        'department_id',
        'assigned_users',
        'tags',
        'priority',
        'expiry_date',
        'confidential_level',
        'download_count'
    ];

    protected $casts = [
        'folder_id' => 'integer',
        'uploaded_by' => 'integer',
        'department_id' => 'integer',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'expiry_date' => 'date'
    ];

    public function folder()
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignments()
    {
        return $this->hasMany(FileUserAssignment::class, 'file_id');
    }

    /**
     * Get user assignments (alias for assignments)
     */
    public function userAssignments()
    {
        return $this->hasMany(FileUserAssignment::class, 'file_id');
    }

    public function accessRequests()
    {
        return $this->hasMany(FileAccessRequest::class, 'file_id');
    }

    public function activities()
    {
        return $this->hasMany(FileActivity::class, 'file_id');
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) return false;
        return now()->isAfter($this->expiry_date);
    }

    public function getFormattedSizeAttribute()
    {
        if ($this->file_size == 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($this->file_size, 1024));
        return round($this->file_size / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Get users assigned to this file
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'file_user_assignments', 'file_id', 'user_id')
                    ->withPivot(['permission_level', 'assigned_at', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get file type from mime type
     */
    public function getFileTypeAttribute()
    {
        if (!$this->mime_type) return 'unknown';
        
        $mimeToType = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'image',
            'image/png' => 'image',
            'image/gif' => 'image',
            'application/msword' => 'document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
            'application/vnd.ms-excel' => 'spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'spreadsheet',
            'text/plain' => 'text',
            'application/zip' => 'archive',
            'application/x-rar-compressed' => 'archive',
        ];
        
        return $mimeToType[$this->mime_type] ?? 'other';
    }

    /**
     * Scope for files accessible by user
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
                  $q3->whereHas('assignedUsers', function($q4) use ($user) {
                      $q4->where('user_id', $user->id);
                  });
              });
        });
    }

    /**
     * Scope for files by type
     */
    public function scopeOfType($query, $type)
    {
        $mimeTypes = [
            'pdf' => ['application/pdf'],
            'image' => ['image/jpeg', 'image/png', 'image/gif'],
            'document' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'spreadsheet' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'text' => ['text/plain'],
            'archive' => ['application/zip', 'application/x-rar-compressed'],
        ];
        
        if (isset($mimeTypes[$type])) {
            return $query->whereIn('mime_type', $mimeTypes[$type]);
        }
        
        return $query;
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Get uploaded by user (alias for uploader)
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file name (alias for original_name)
     */
    public function getNameAttribute()
    {
        return $this->original_name;
    }
}

