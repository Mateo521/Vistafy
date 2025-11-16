<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_date',
        'location',
        'is_private',
        'private_token',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_private' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function photos()
    {
        return $this->belongsToMany(Photo::class)->withPivot('order')->withTimestamps()->orderBy('order');
    }

    // Generar slug y token automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
            
            if ($event->is_private && empty($event->private_token)) {
                $event->private_token = Str::random(32);
            }
        });
    }

    // Accessor para URL pública
    public function getPublicUrlAttribute()
    {
        return route('events.show', $this->slug);
    }

    // Accessor para URL privada
    public function getPrivateUrlAttribute()
    {
        return $this->is_private ? route('events.private', $this->private_token) : null;
    }
}
