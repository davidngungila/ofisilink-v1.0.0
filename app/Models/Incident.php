<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_code',  // Existing table uses incident_code
        'incident_no',    // New field for consistency
        'subject',        // Existing table uses subject
        'title',          // New field for consistency
        'description',
        'priority',
        'status',
        'category',
        'reported_by',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'assigned_to',
        'assigned_at',
        'assigned_by',
        'resolution_notes',
        'resolution_details',  // Existing table uses resolution_details
        'resolved_at',
        'resolved_by',
        'closed_at',
        'closed_by',
        'source',
        'email_message_id',
        'email_thread_id',
        'email_received_at',
        'attachments',
        'custom_fields',
        'internal_notes',
        'created_by',
        'updated_by',
        'due_date'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'email_received_at' => 'datetime',
        'attachments' => 'array',
        'custom_fields' => 'array',
    ];

    /**
     * Generate unique incident number in format: INC20251104-001
     */
    public static function generateIncidentNo()
    {
        $date = date('Ymd'); // Format: 20251104
        $prefix = 'INC' . $date . '-';
        
        // Get the last incident for today - only use incident_code column (incident_no is handled via accessor)
        $lastIncident = self::whereDate('created_at', today())
            ->where('incident_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastIncident && $lastIncident->incident_code) {
            // Extract the sequential number from the last incident
            // Extract number after the dash (e.g., "001" from "INC20251104-001")
            $lastCode = $lastIncident->incident_code;
            $dashPos = strrpos($lastCode, '-');
            if ($dashPos !== false) {
                $lastNumber = (int) substr($lastCode, $dashPos + 1);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
        } else {
            // First incident of the day
            $nextNumber = 1;
        }
        
        // Format: INC20251104-001 (3 digits)
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method to auto-generate incident number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($incident) {
            if (empty($incident->incident_code)) {
                $code = self::generateIncidentNo();
                $incident->incident_code = $code;
                // Note: incident_no is handled via accessor/mutator, no need to set it directly
            }
            // Use subject if title not provided (backward compatibility)
            if (empty($incident->title) && !empty($incident->subject)) {
                $incident->title = $incident->subject;
            } elseif (empty($incident->subject) && !empty($incident->title)) {
                $incident->subject = $incident->title;
            }
        });
    }
    
    /**
     * Get the title attribute (backward compatibility - maps to subject)
     */
    public function getTitleAttribute()
    {
        return $this->attributes['subject'] ?? null;
    }
    
    /**
     * Set the title attribute (backward compatibility - maps to subject)
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['subject'] = $value;
    }
    
    /**
     * Get the incident number attribute (backward compatibility)
     */
    public function getIncidentNoAttribute()
    {
        return $this->attributes['incident_code'] ?? null;
    }
    
    /**
     * Set the incident number attribute (backward compatibility)
     */
    public function setIncidentNoAttribute($value)
    {
        $this->attributes['incident_code'] = $value;
    }

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(IncidentUpdate::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['New', 'Assigned', 'In Progress']);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['Resolved', 'Closed', 'Cancelled']);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, ['Resolved', 'Closed', 'Cancelled']);
    }

    public function getResolutionTimeInDays()
    {
        if (!$this->resolved_at || !$this->created_at) {
            return null;
        }
        return $this->created_at->diffInDays($this->resolved_at);
    }

    public function getDaysOpen()
    {
        if (!$this->created_at) {
            return null;
        }
        return $this->created_at->diffInDays(now());
    }

    public function hasAttachments()
    {
        return $this->attachments && count($this->attachments) > 0;
    }

    public function canBeEditedBy($user)
    {
        return $user->hasAnyRole(['HR Officer', 'System Admin']) ||
               ($user->hasRole('HOD') && $this->isInSameDepartment($user)) ||
               $this->assigned_to === $user->id;
    }

    public function canBeViewedBy($user)
    {
        return $user->hasAnyRole(['HR Officer', 'System Admin']) ||
               ($user->hasRole('HOD') && $this->isInSameDepartment($user)) ||
               $this->assigned_to === $user->id ||
               $this->reported_by === $user->id;
    }

    protected function isInSameDepartment($user)
    {
        if (!$user->primary_department_id) {
            return false;
        }

        if ($this->assignedTo && $this->assignedTo->primary_department_id === $user->primary_department_id) {
            return true;
        }

        if ($this->reporter && $this->reporter->primary_department_id === $user->primary_department_id) {
            return true;
        }

        return false;
    }
}
