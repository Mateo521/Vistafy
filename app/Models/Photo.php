<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Photo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unique_id',
        'photographer_id',
        'title',
        'description',
        'price',
        'original_path',
        'watermarked_path',
        'thumbnail_path',
        'is_active',
        'downloads',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'downloads' => 'integer',
    ];

    // Relaciones
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class)->withPivot('order')->withTimestamps();
    }

    // Generar ID única automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($photo) {
            if (empty($photo->unique_id)) {
                $photo->unique_id = 'PHT-' . strtoupper(uniqid());
            }
        });
    }

    // Accessor para URL de imagen con marca de agua
    public function getWatermarkedUrlAttribute()
    {
        return asset('storage/' . $this->watermarked_path);
    }

    // Accessor para URL de thumbnail
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->watermarked_url;
    }
}
