<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Photographer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'region',
        'bio',
        'profile_photo',
        'cover_photo',
        'phone',
        'is_active',
        'is_verified',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Boot method para generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($photographer) {
            if (empty($photographer->slug)) {
                $photographer->slug = Str::slug($photographer->business_name);
                
                // Asegurar que el slug sea único
                $count = 1;
                while (static::where('slug', $photographer->slug)->exists()) {
                    $photographer->slug = Str::slug($photographer->business_name) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Usuario asociado al fotógrafo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Eventos del fotógrafo
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Fotos del fotógrafo
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Obtener el email del fotógrafo desde el usuario
     */
    public function getEmailAttribute()
    {
        return $this->user->email ?? null;
    }

    /**
     * Obtener URL de la foto de perfil
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return null;
    }

    /**
     * Obtener URL de la foto de portada
     */
    public function getCoverPhotoUrlAttribute()
    {
        if ($this->cover_photo) {
            return asset('storage/' . $this->cover_photo);
        }
        return null;
    }

    /**
     * Scope para fotógrafos verificados
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope para fotógrafos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener la región formateada
     */
    public function getFormattedRegionAttribute()
    {
        return $this->region ?? 'Sin especificar';
    }
}
