<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id',
        'name',
        'slug',
        'description',
        'long_description',
        'event_date',
        'location',
        'is_private',
        'cover_image',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_private' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    /**
     * Generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name) . '-' . Str::random(6);
            }
        });

        static::deleting(function ($event) {
            // Eliminar imagen de portada al eliminar evento
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
        });
    }

    /**
     * Relación con fotógrafo
     */
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    /**
     * Relación muchos a muchos con fotos
     */
    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'event_photo');
    }

    /**
     * Obtener todos los fotógrafos únicos del evento
     */
    public function photographers()
    {
        return Photographer::whereIn('id', function($query) {
            $query->select('photographer_id')
                ->from('photos')
                ->join('event_photo', 'photos.id', '=', 'event_photo.photo_id')
                ->where('event_photo.event_id', $this->id);
        })->with('user:id,name')->get();
    }

    /**
     * URL de la imagen de portada
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return Storage::disk('public')->url($this->cover_image);
        }

        // Imagen por defecto o la primera foto del evento
        $firstPhoto =$this->photos()->first();
        return $firstPhoto ? $firstPhoto->thumbnail_url : 'https://via.placeholder.com/800x400?text=Sin+Portada';
    }

    /**
     * Obtener estadísticas del evento
     */
    public function getStatsAttribute()
    {
        return [
            'total_photos' => $this->photos()->count(),
            'total_photographers' => $this->photographers()->count(),
            'total_downloads' => $this->photos()->sum('downloads'),
        ];
    }
}
