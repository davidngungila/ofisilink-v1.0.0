<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_no', 'entry_date', 'reference_no', 'description', 'status',
        'source', 'source_ref', 'created_by', 'approved_by', 'approved_at',
        'posted_by', 'posted_at', 'notes'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    // Relationships
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'Posted');
    }

    // Helper methods
    public function isBalanced(): bool
    {
        $debits = $this->lines()->where('type', 'Debit')->sum('amount');
        $credits = $this->lines()->where('type', 'Credit')->sum('amount');
        return abs($debits - $credits) < 0.01; // Allow for floating point precision
    }

    public function getTotalDebitsAttribute()
    {
        return $this->lines()->where('type', 'Debit')->sum('amount');
    }

    public function getTotalCreditsAttribute()
    {
        return $this->lines()->where('type', 'Credit')->sum('amount');
    }

    public function canBePosted(): bool
    {
        return $this->status === 'Draft' && $this->isBalanced();
    }

    public function post(): bool
    {
        if (!$this->canBePosted()) {
            return false;
        }

        \DB::transaction(function () {
            foreach ($this->lines as $line) {
                GeneralLedger::create([
                    'account_id' => $line->account_id,
                    'transaction_date' => $this->entry_date,
                    'reference_type' => 'JournalEntry',
                    'reference_id' => $this->id,
                    'reference_no' => $this->entry_no,
                    'type' => $line->type,
                    'amount' => $line->amount,
                    'description' => $line->description ?? $this->description,
                    'source' => $this->source,
                    'created_by' => $this->created_by,
                ]);
            }

            $this->update([
                'status' => 'Posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);
        });

        return true;
    }

    public static function generateEntryNo(): string
    {
        $date = date('Ymd');
        $last = self::whereDate('created_at', today())
            ->where('entry_no', 'like', "JE{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $sequence = (int) substr($last->entry_no, -4) + 1;
        } else {
            $sequence = 1;
        }

        return "JE{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}



