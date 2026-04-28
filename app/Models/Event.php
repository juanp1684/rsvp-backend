<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{

    protected $fillable = [
        'name',
        'partner1_parents',
        'partner2_parents',
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
        'couple_mobile_image',
        'ceremony_image',
        'reception_image',
        'invitation_image',
        'late_rsvp_deadline',
        'no_kids',
        'no_kids_message',
        'confirm_attending_image',
        'confirm_declined_image',
        'confirm_attending_message',
        'confirm_declined_message',
        'song',
    ];

    protected $casts = [
        'ceremony_at' => 'datetime',
        'reception_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
        'late_rsvp_deadline' => 'datetime',
        'no_kids' => 'boolean',
    ];

    public function invitees(): HasMany
    {
        return $this->hasMany(Invitee::class);
    }
}
