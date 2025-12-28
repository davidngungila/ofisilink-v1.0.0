<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    protected $table = 'database_backups';
    
    // Disable updated_at since the table doesn't have this column
    const UPDATED_AT = null;
    
    protected $fillable = [
        'filename',
        'file_path',
        'file_size',
        'status',
        'error_message',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who created the backup
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if backup file exists
     */
    public function fileExists(): bool
    {
        if (empty($this->file_path)) {
            return false;
        }
        
        // Use direct filesystem check for better reliability (handles Windows path issues)
        try {
            // Normalize path separators
            $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->file_path);
            $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . trim($normalizedPath, DIRECTORY_SEPARATOR));
            
            // Try to resolve real path
            $realPath = realpath($fullPath);
            if ($realPath !== false) {
                return file_exists($realPath) && is_file($realPath);
            }
            
            // Fallback: check if file exists
            return file_exists($fullPath) && is_file($fullPath);
        } catch (\Exception $e) {
            \Log::warning('Error checking backup file existence', [
                'file_path' => $this->file_path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
