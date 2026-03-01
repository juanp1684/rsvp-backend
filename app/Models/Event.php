<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'ceremony_at',
        'reception_at',
        'ceremony_location',
        'ceremony_url',
        'reception_location',
        'reception_url',
        'dress_code',
        'rsvp_deadline',
        'notes',
    ];

    protected $casts = [
        'ceremony_at'  => 'datetime',
        'reception_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
    ];
}
