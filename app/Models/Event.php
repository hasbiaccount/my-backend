<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
