<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id',
        'event_id',
        'unique_id',
        'title',
        'description',
        'original_path',
        'watermarked_path',
        'thumbnail_path',
        'original_name',
        'file_size',
        'width',
        'height',
        'mime_type',
        'price',
        'is_active',
        'downloads',
        'views',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'downloads' => 'integer',
        'views' => 'integer',
    ];


    protected $appends = [
        'thumbnail_url',
        'watermarked_url',
        'original_url',
        'view_url',
        'thumbnail_view_url',
    ];


    /**
     * Resolver el binding por unique_id
     */



    // Relaciones
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getOriginalUrlAttribute()
    {
        if (!$this->original_path) {
            return null;
        }

        if (Str::startsWith($this->original_path, ['http://', 'https://'])) {
            return $this->original_path;
        }

        return asset('storage/' . $this->original_path);
    }
    //  ACCESSORS PARA URLs PÚBLICAS
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail_path) {
            return 'https://via.placeholder.com/400?text=Sin+Imagen';
        }

        if (Str::startsWith($this->thumbnail_path, ['http://', 'https://'])) {
            return $this->thumbnail_path;
        }

        return asset('storage/' . $this->thumbnail_path);
    }

    public function getWatermarkedUrlAttribute()
    {
        if (!$this->watermarked_path) {
            return $this->thumbnail_url; // Fallback
        }

        if (Str::startsWith($this->watermarked_path, ['http://', 'https://'])) {
            return $this->watermarked_path;
        }

        return asset('storage/' . $this->watermarked_path);
    }


    // ========================================
    //  NUEVOS ACCESSORS PARA EL VISOR
    // ========================================

    /**
     * URL para el visor de foto con marca de agua
     * Formato: /foto/{photographer}/{year}/{month}/{day}/watermarked/{filename}
     */
    public function getViewUrlAttribute()
    {
        if (!$this->watermarked_path) {
            return $this->watermarked_url; // Fallback a URL directa
        }

        // Extraer componentes de la ruta
        // Formato esperado: photos/4/2025/12/11/watermarked/WRA8GL_watermarked.jpg
        if (preg_match('/photos\/(\d+)\/(\d{4})\/(\d{2})\/(\d{2})\/(watermarked|thumbnails)\/(.+)$/', $this->watermarked_path, $matches)) {
            return route('photo.view', [
                'photographer' => $matches[1],
                'year' => $matches[2],
                'month' => $matches[3],
                'day' => $matches[4],
                'type' => 'watermarked',
                'filename' => $matches[6],
            ]);
        }

        // Si no coincide el patrón, usar URL de storage directa
        return $this->watermarked_url;
    }

    /**
     * URL para el visor de thumbnail
     */
    public function getThumbnailViewUrlAttribute()
    {
        if (!$this->thumbnail_path) {
            return $this->thumbnail_url; // Fallback
        }

        // Extraer componentes de la ruta
        if (preg_match('/photos\/(\d+)\/(\d{4})\/(\d{2})\/(\d{2})\/(thumbnails)\/(.+)$/', $this->thumbnail_path, $matches)) {
            return route('photo.view', [
                'photographer' => $matches[1],
                'year' => $matches[2],
                'month' => $matches[3],
                'day' => $matches[4],
                'type' => 'thumbnails',
                'filename' => $matches[6],
            ]);
        }

        // Fallback a URL directa
        return $this->thumbnail_url;
    }

}
