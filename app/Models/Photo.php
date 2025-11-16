<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'photographer_id',
        'unique_id',
        'title',
        'description',
        'original_path',      // ✅ Sin marca (solo después de pagar)
        'watermarked_path',   // ✅ Con marca (preview gratis)
        'thumbnail_path',     // ✅ Miniatura
        'original_name',
        'file_size',
        'width',
        'height',
        'price',
        'downloads',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'downloads' => 'integer',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    // ✅ AGREGAR ESTOS ACCESSORS
    protected $appends = [
        'thumbnail_url',
        'preview_url',      // Esta mostrará la versión CON marca
        'download_url',     // Esta mostrará la versión SIN marca (solo si pagó)
    ];

    /**
     * URL de la miniatura (con marca de agua)
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }
        return null;
    }

    /**
     * URL del preview (con marca de agua)
     */
    public function getPreviewUrlAttribute()
    {
        if ($this->preview_path) {
            return Storage::disk('public')->url($this->preview_path);
        }
        return null;
    }

    /**
     * URL del original (sin marca de agua - solo después de pagar)
     */
    public function getOriginalUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    /**
     * Relación: Una foto pertenece a un fotógrafo
     */
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    /**
     * Relación: Una foto puede estar en múltiples eventos
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_photo')
            ->withTimestamps();
    }

    /**
     * Relación: Una foto puede tener múltiples ventas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getDownloadUrlAttribute()
    {
        if ($this->original_path) {
            return Storage::disk('public')->url($this->original_path);
        }
        return null;
    }

    /**
     * Verificar si el usuario compró esta foto
     */
    public function isPurchasedBy($user)
    {
        if (!$user)
            return false;

        return $this->sales()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
    }

}
