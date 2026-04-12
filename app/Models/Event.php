<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
// Accessor para URL de portada 100% en la nube
    public function getCoverImageUrlAttribute()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('b2');
        $path = null;

    
        if ($this->cover_image) {
     
            if (\Illuminate\Support\Str::startsWith($this->cover_image, ['http://', 'https://'])) {
                return $this->cover_image;
            }
            $path = $this->cover_image;
        } 
        
        else {
            $firstPhoto = $this->relationLoaded('photos') && $this->photos->isNotEmpty()
                ? $this->photos->first()
                : $this->photos()->where('is_active', true)->first();

            if ($firstPhoto) {
                $path = $firstPhoto->thumbnail_path ?? $firstPhoto->watermarked_path ?? $firstPhoto->original_path;
            }
        }

        
        return $path ? $disk->temporaryUrl($path, now()->addMinutes(60)) : null;
    }


 
    public function owner()
    {
        return $this->belongsTo(Photographer::class, 'photographer_id');
    }

    // 2. Los Colaboradores (Nueva relación)
    public function collaborators()
    {
        return $this->belongsToMany(Photographer::class, 'event_photographer')
                    ->withTimestamps();
    }
    
    // Helper para saber quiénes pueden subir fotos (Dueño + Colaboradores)
    public function canUpload(Photographer $photographer)
    {
        return $this->photographer_id === $photographer->id || 
               $this->collaborators->contains($photographer->id);
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
