<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'photographer_id',
        'message'
    ];

    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}