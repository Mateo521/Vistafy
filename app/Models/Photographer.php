<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Photographer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_name',
        'phone',
        'region',
        'bio',
        'profile_photo',
        'cover_photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación: Un fotógrafo pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un fotógrafo tiene muchas fotos
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Relación: Un fotógrafo tiene muchos eventos
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Accessor para URL de foto de perfil
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return Storage::disk('public')->url($this->profile_photo);
        }
        
        // Imagen por defecto
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->business_name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Accessor para URL de foto de portada
     */
    public function getCoverPhotoUrlAttribute()
    {
        if ($this->cover_photo) {
            return Storage::disk('public')->url($this->cover_photo);
        }
        
        return null;
    }
}
