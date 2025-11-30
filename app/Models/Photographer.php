<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
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
        'status', // ✅ Nuevo
        'rejection_reason', // ✅ Nuevo
        'approved_at', // ✅ Nuevo
        'approved_by', // ✅ Nuevo
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // ✅ Scopes para filtrar por estado
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }


    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
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
        if (!$this->profile_photo) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->business_name) . '&size=200&background=6366f1&color=fff';
        }

        // ✅ Usar Storage::url() directamente
        return Storage::url($this->profile_photo);
    }


    /**
     * Obtener URL de la foto de portada
     */
    public function getCoverPhotoUrlAttribute()
    {
        if (!$this->cover_photo) {
            return null;
        }

        // ✅ Usar Storage::url() directamente
        return Storage::url($this->cover_photo);
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
