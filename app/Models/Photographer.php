<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photographer extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'region',
        'bio',
        'phone',
        'profile_photo',
        'cover_photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    // Accessors
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo 
            ? asset('storage/' . $this->profile_photo) 
            : null;
    }

    public function getCoverPhotoUrlAttribute()
    {
        return $this->cover_photo 
            ? asset('storage/' . $this->cover_photo) 
            : null;
    }

    // Stats
    public function getTotalPhotosAttribute()
    {
        return $this->photos()->count();
    }

    public function getActivePhotosAttribute()
    {
        return $this->photos()->where('is_active', true)->count();
    }

    public function getTotalDownloadsAttribute()
    {
        return $this->photos()->sum('downloads');
    }

    public function getTotalEventsAttribute()
    {
        return Event::whereHas('photos', function($q) {
            $q->where('photographer_id', $this->id);
        })->count();
    }
}
