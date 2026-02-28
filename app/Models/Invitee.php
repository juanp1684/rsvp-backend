<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitee extends Model
{
    use HasUuids;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'code',
        'allowed_companions',
        'status',
        'notes',
    ];

    protected $casts = [
        'allowed_companions' => 'integer',
    ];

    public function companions(): HasMany
    {
        return $this->hasMany(Companion::class);
    }
}
