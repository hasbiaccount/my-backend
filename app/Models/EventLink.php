<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'url',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
