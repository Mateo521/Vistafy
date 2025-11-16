<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'photographer_id',   
        'name',
        'slug',
        'description',
        'event_date',
        'location',
        'is_private',
        'access_code',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_private' => 'boolean',
    ];

    /**
     * Generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });
    }

    /**
     * Relación: Un evento pertenece a un fotógrafo
     */
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    /**
     * Relación: Un evento tiene muchas fotos
     */
    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'event_photo')
            ->withTimestamps();
    }
}
