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




}
