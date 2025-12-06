<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Facades\Storage;
class Photographer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'region',
        'latitude',  // <--- AGREGAR
        'longitude', // <--- AGREGAR
        'bio',
        'phone',
        'website',
        'instagram',
        'facebook',

        'profile_photo',      //  NUEVO
        'banner_photo',       //  NUEVO
        'is_active',
        'is_verified',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
    ];


    const REGION_COORDINATES = [
        'Buenos Aires' => ['lat' => -34.6037, 'lng' => -58.3816],
        'CABA' => ['lat' => -34.6037, 'lng' => -58.3816],
        'Córdoba' => ['lat' => -31.4201, 'lng' => -64.1888],
        'Santa Fe' => ['lat' => -31.6107, 'lng' => -60.6973],
        'Mendoza' => ['lat' => -32.8895, 'lng' => -68.8458],
        'Tucumán' => ['lat' => -26.8083, 'lng' => -65.2176],
        'Rosario' => ['lat' => -32.9442, 'lng' => -60.6505],
        'Salta' => ['lat' => -24.7821, 'lng' => -65.4232],
        'Neuquén' => ['lat' => -38.9516, 'lng' => -68.0591],
        'Entre Ríos' => ['lat' => -31.7413, 'lng' => -60.5115],
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'approved_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
        'banner_photo_url',
    ];
    //  Scopes para filtrar por estado
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

    public function guestEvents()
    {
        return $this->belongsToMany(Event::class, 'event_photographer')
            ->withTimestamps();
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

        // Antes de guardar (crear o editar), asignar coordenadas
        static::saving(function ($photographer) {
            if ($photographer->isDirty('region') && $photographer->region) {
                // Buscamos si la región existe en nuestra lista
                $coords = self::REGION_COORDINATES[$photographer->region] ?? null;

                if ($coords) {
                    $photographer->latitude = $coords['lat'];
                    $photographer->longitude = $coords['lng'];
                }
            }
        });


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
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return Storage::url($this->profile_photo);
        }
        return null;
    }



    public function getBannerPhotoUrlAttribute()
    {
        if ($this->banner_photo && Storage::disk('public')->exists($this->banner_photo)) {
            return Storage::url($this->banner_photo);
        }
        return null;
    }

    /**
     * Usar slug por defecto para rutas públicas
     */
    public function getRouteKeyName()
    {
        // Si estamos en una ruta de admin, usar ID
        if (request()->is('admin/*')) {
            return 'id';
        }

        // Para rutas públicas, usar slug
        return 'slug';
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
