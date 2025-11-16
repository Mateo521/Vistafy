<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id',
        'cover_photo_id',  // ← Relación con la foto de portada
        'name',
        'slug',
        'description',
        'event_date',
        'location',
        'is_active',
        // ... más campos
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ✅ Agregar estos appends para que siempre estén disponibles
    protected $appends = [
        'cover_image_url',
    ];

    // Relaciones
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    // ✅ Relación con la foto de portada
    public function coverPhoto()
    {
        return $this->belongsTo(Photo::class, 'cover_photo_id');
    }

    // ✅ Accessor para obtener la URL de la imagen de portada
    public function getCoverImageUrlAttribute()
    {
        if ($this->coverPhoto && $this->coverPhoto->thumbnail_path) {
            return asset('storage/' . $this->coverPhoto->thumbnail_path);
        }
        
        // Si no hay foto de portada, usar la primera foto del evento
        $firstPhoto =$this->photos()->first();
        if ($firstPhoto && $firstPhoto->thumbnail_path) {
            return asset('storage/' . $firstPhoto->thumbnail_path);
        }
        
        // Si no hay fotos, usar placeholder
        return asset('images/event-placeholder.png');
    }


   

    // Generar slug automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('name')) {
                $event->slug = Str::slug($event->name);
            }
        });
    }
}
