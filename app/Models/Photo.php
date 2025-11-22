<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    //  ACCESSORS PARA URLs PÚBLICAS
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : 'https://via.placeholder.com/400x400?text=Sin+Imagen';
    }

    public function getWatermarkedUrlAttribute()
    {
        return $this->watermarked_path
            ? asset('storage/' . $this->watermarked_path)
            : null;
    }

    public function getOriginalUrlAttribute()
    {
        return $this->original_path
            ? asset('storage/' . $this->original_path)
            : null;
    }

    
}
