<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Companion extends Model
{
    use HasUuids;

    protected $fillable = [
        'invitee_id',
        'full_name',
    ];

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(Invitee::class);
    }
}
