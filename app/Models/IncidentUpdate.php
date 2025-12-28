<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentUpdate extends Model
{
	use HasFactory;

	protected $fillable = [
		'incident_id',
		'user_id',
		'update_text',
		'is_internal_note',
	];

	protected $casts = [
		'is_internal_note' => 'boolean',
	];

	public function incident(): BelongsTo
	{
		return $this->belongsTo(Incident::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}









