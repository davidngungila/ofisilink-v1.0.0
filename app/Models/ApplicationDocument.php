<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'document_type',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
    ];

    /**
     * Get the application this document belongs to
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
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
}

