<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id', 'event_id', 'unique_id', 'title', 'description',
        'original_path', 'watermarked_path', 'thumbnail_path', 'original_name',
        'file_size', 'width', 'height', 'mime_type', 'price', 'is_active',
        'downloads', 'views', 'face_encodings', 'has_faces', 'faces_processed_at',
        'bib_numbers', 'bib_processed', 'bib_processed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'face_encodings' => 'array',
        'bib_numbers' => 'array',
        'has_faces' => 'boolean',
        'bib_processed' => 'boolean',
        'faces_processed_at' => 'datetime',
        'bib_processed_at' => 'datetime',
    ];

  
    protected $appends = [
        'thumbnail_url',
        'watermarked_url',
        'original_url',
        // 'view_url',  
        // 'thumbnail_view_url',
    ];

    // Relaciones
    public function photographer() { return $this->belongsTo(Photographer::class); }
    public function event() { return $this->belongsTo(Event::class); }

    /**
     * URL de la foto Original (Privada en B2)
     */
   public function getOriginalUrlAttribute()
    {
        if (!$this->original_path) return null;

        if (\Illuminate\Support\Str::startsWith($this->original_path, ['http://', 'https://'])) {
            return $this->original_path;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('b2');
        return $disk->temporaryUrl($this->original_path, now()->addMinutes(60));
    }

    /**
     * URL del Thumbnail (Pública en B2)
     */
   public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail_path) {
            return 'https://via.placeholder.com/400?text=Sin+Imagen';
        }

        if (\Illuminate\Support\Str::startsWith($this->thumbnail_path, ['http://', 'https://'])) {
            return $this->thumbnail_path;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('b2');
        return $disk->temporaryUrl($this->thumbnail_path, now()->addMinutes(60));
    }

    /**
     * URL con Marca de Agua (Pública en B2)
     */
  public function getWatermarkedUrlAttribute()
    {
        if (!$this->watermarked_path) return $this->thumbnail_url;

        if (\Illuminate\Support\Str::startsWith($this->watermarked_path, ['http://', 'https://'])) {
            return $this->watermarked_path;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('b2');
        return $disk->temporaryUrl($this->watermarked_path, now()->addMinutes(60));
    }

    
}