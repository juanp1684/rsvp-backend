<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitee extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'type',
        'full_name',
        'phone',
        'code',
        'allowed_companions',
        'status',
        'notes',
        'invitation_sent',
    ];

    protected $casts = [
        'allowed_companions' => 'integer',
        'invitation_sent'    => 'boolean',
    ];

    public function companions(): HasMany
    {
        return $this->hasMany(Companion::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
