<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (! $event->slug) {
                $event->slug = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'ceremony_at',
        'reception_at',
        'ceremony_location',
        'ceremony_url',
        'reception_location',
        'reception_url',
        'dress_code',
        'rsvp_deadline',
        'notes',
        'couple_image',
        'ceremony_image',
        'reception_image',
        'invitation_image',
    ];

    protected $casts = [
        'ceremony_at'  => 'datetime',
        'reception_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
    ];

    public function invitees(): HasMany
    {
        return $this->hasMany(Invitee::class);
    }
}
