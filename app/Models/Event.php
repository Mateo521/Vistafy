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
        'long_description',
        'cover_image',
        'event_date',
        'location',
        'is_private',
        'private_token',
        'is_active',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_active' => 'boolean',
        'event_date' => 'date',
    ];
    protected $appends = ['cover_image_url'];
    // Relaciones
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    // Accessor para URL de portada
    public function getCoverImageUrlAttribute()
    {
        // 1. Cover image directo
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }

        // 2. Primera foto del evento (ya cargada con with())
        if ($this->relationLoaded('photos') && $this->photos->isNotEmpty()) {
            $photo = $this->photos->first();
            $path = $photo->thumbnail_path ?? $photo->watermarked_path ?? $photo->original_path;
            return $path ? asset('storage/' . $path) : null;
        }

        // 3. Fallback: buscar primera foto
        $firstPhoto = $this->photos()->where('is_active', true)->first();
        if ($firstPhoto) {
            $path = $firstPhoto->thumbnail_path ?? $firstPhoto->watermarked_path ?? $firstPhoto->original_path;
            return $path ? asset('storage/' . $path) : null;
        }

        return null;
    }


    // Auto-generar slug y token
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name) . '-' . Str::random(6);
            }

            if ($event->is_private && empty($event->private_token)) {
                $event->private_token = Str::random(32);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('name')) {
                $event->slug = Str::slug($event->name) . '-' . Str::random(6);
            }

            // Generar token si se volvió privado
            if ($event->is_private && empty($event->private_token)) {
                $event->private_token = Str::random(32);
            }

            // Eliminar token si ya no es privado
            if (!$event->is_private && !empty($event->private_token)) {
                $event->private_token = null;
            }
        });
    }
}
