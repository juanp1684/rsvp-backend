<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{

    protected $fillable = [
        'name',
        'partner1_parent1',
        'partner1_parent2',
        'partner2_parent1',
        'partner2_parent2',
        'slug',
        'ceremony_at',
        'ceremony_location',
        'ceremony_url',
        'civil_at',
        'civil_location',
        'civil_url',
        'civil_image',
        'reception_at',
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
        'civil_ceremony_same_venue',
        'civil_reception_same_venue',
        'ceremony_reception_same_venue',
        'subtitle',
        'dress_code_image',
        'gift_suggestion',
        'gift_suggestion_image',
        'recommendations',
    ];

    protected $casts = [
        'ceremony_at' => 'datetime',
        'civil_at' => 'datetime',
        'reception_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
        'late_rsvp_deadline' => 'datetime',
        'no_kids' => 'boolean',
        'civil_ceremony_same_venue' => 'boolean',
        'civil_reception_same_venue' => 'boolean',
        'ceremony_reception_same_venue' => 'boolean',
    ];

    public function invitees(): HasMany
    {
        return $this->hasMany(Invitee::class);
    }
}
