<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'issue_date',
        'expiry_date',
        'issued_by',
        'document_number',
        'description',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the employee (user) that owns this document
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        if ($this->file_size) {
            $units = ['B', 'KB', 'MB', 'GB'];
            $size = $this->file_size;
            $unit = 0;
            
            while ($size >= 1024 && $unit < count($units) - 1) {
                $size /= 1024;
                $unit++;
            }
            
            return round($size, 2) . ' ' . $units[$unit];
        }
        
        return '0 B';
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        if (Storage::exists($this->file_path)) {
            return Storage::url($this->file_path);
        }
        return asset('storage/' . $this->file_path);
    }

    /**
     * Check if document is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= 30;
    }
}
