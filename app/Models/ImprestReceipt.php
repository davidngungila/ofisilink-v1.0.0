<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImprestReceipt extends Model
{
    protected $fillable = [
        'assignment_id',
        'receipt_amount',
        'receipt_description',
        'receipt_file_path',
        'submitted_by',
        'submitted_at',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_notes'
    ];

    protected $casts = [
        'receipt_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime'
    ];

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(ImprestAssignment::class, 'assignment_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
