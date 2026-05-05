<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organizer_id',
    'title',
    'description',
    'start_date',
    'end_date',
    'location',
    'max_participants',
    'registration_fee',
    'registration_open',
    'registration_deadline',
])]
class Event extends Model
{
    use HasFactory;

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'event_id', 'id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function cartAcaras(): HasMany
    {
        return $this->hasMany(CartAcara::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
}
